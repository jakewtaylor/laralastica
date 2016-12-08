<?php

namespace Michaeljennings\Laralastica\Drivers;

use Elastica\Client;
use Elastica\Index;
use Elastica\Query as ElasticaQuery;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Common;
use Elastica\Query\Fuzzy;
use Elastica\Query\Match;
use Elastica\Query\MatchAll;
use Elastica\Query\QueryString;
use Elastica\Query\Range;
use Elastica\Query\Regexp;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Query\Wildcard;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Search;
use Michaeljennings\Laralastica\Contracts\Driver;
use Michaeljennings\Laralastica\Contracts\Query;
use Michaeljennings\Laralastica\ResultCollection;

class ElasticaDriver implements Driver
{
    /**
     * The elastica client.
     *
     * @var Client
     */
    protected $client;

    /**
     * The elastica index.
     *
     * @var Index
     */
    protected $index;

    public function __construct(Client $client, Index $index)
    {
        $this->client = $client;
        $this->index = $index;
    }

    /**
     * Create a common query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html
     *
     * @param string        $field
     * @param string        $query
     * @param float         $cutoffFrequency
     * @param callable|null $callback
     * @return Common
     */
    public function common($field, $query, $cutoffFrequency, callable $callback = null)
    {
        $query = new Common($field, $query, $cutoffFrequency);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new fuzzy query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html
     *
     * @param string        $field
     * @param string        $value
     * @param callable|null $callback
     * @return Fuzzy
     */
    public function fuzzy($field, $value, callable $callback = null)
    {
        $query = new Fuzzy($field, $value);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new match query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html
     *
     * @param string|null   $field
     * @param string|null   $value
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function match($field = null, $value = null, callable $callback = null)
    {
        $query = new Match($field, $value);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a match all query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
     *
     * @return MatchAll
     */
    public function matchAll()
    {
        return new MatchAll();
    }

    /**
     * Create a query string query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-query-string-query.html
     *
     * @param string        $query
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function queryString($query = '', callable $callback = null)
    {
        $query = new QueryString($query);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a range query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html
     *
     * @param null|string   $fieldName
     * @param array         $args
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function range($fieldName = null, $args = [], callable $callback = null)
    {
        $query = new Range($fieldName, $args);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new regular expression query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     *
     * @param string        $key
     * @param string|null   $value
     * @param float         $boost
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function regexp($key = '', $value = null, $boost = 1.0, callable $callback = null)
    {
        $query = new Regexp($key, $value, $boost);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new term query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html
     *
     * @param array         $terms
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function term(array $terms = [], callable $callback = null)
    {
        $query = new Term($terms);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new terms query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html
     *
     * @param string        $key
     * @param array         $terms
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function terms($key = '', array $terms = [], callable $callback = null)
    {
        $query = new Terms($key, $terms);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Create a new wildcard query.
     *
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html
     *
     * @param string        $key
     * @param string|null   $value
     * @param float         $boost
     * @param callable|null $callback
     * @return AbstractQuery
     */
    public function wildcard($key = '', $value = null, $boost = 1.0, callable $callback = null)
    {
        $query = new Wildcard($key, $value, $boost);

        return $this->returnQuery($query, $callback);
    }

    /**
     * Execute the provided queries.
     *
     * @param string|array $types
     * @param array        $queries
     * @return ResultCollection
     */
    public function get($types, array $queries)
    {
        if ( ! is_array($types)) {
            $types = func_get_args();
        }

        $search = new Search($this->client);

        $search->addIndex($this->index);
        $search->addTypes($types);
        $search->setQuery($this->newQuery($queries));

        return $this->newResultCollection($search->search());
    }

    /**
     * Create a new elastica query from an array of queries.
     *
     * @param array $queries
     * @return Query
     */
    protected function newQuery(array $queries)
    {
        if ( ! empty($queries)) {
            $container = new BoolQuery();

            foreach ($queries as $query) {
                $container = $this->addQueryToContainer($query, $container);
            }

            $query = new ElasticaQuery($container);
            $query->addSort('_score');
        } else {
            $query = new ElasticaQuery();
        }

        return $query;
    }

    /**
     * Set the type of match for the query and then add it to the bool container.
     *
     * @param Query     $query
     * @param BoolQuery $container
     * @return BoolQuery
     */
    protected function addQueryToContainer(Query $query, BoolQuery $container)
    {
        switch ($query->getType()) {
            case "must":
                $container->addMust($query->getQuery());
                break;
            case "should":
                $container->addShould($query->getQuery());
                break;
            case "must_not":
                $container->addMustNot($query->getQuery());
                break;
        }

        return $container;
    }

    /**
     * Create a new result collection.
     *
     * @param ResultSet $results
     * @return ResultCollection
     */
    protected function newResultCollection(ResultSet $results)
    {
        $items = [];

        foreach ($results as $result) {
            $items[] = $this->newResult($result);
        }

        return new ResultCollection($items, $results->getTotalHits(), $results->getMaxScore(), $results->getTotalTime());
    }

    /**
     * Create a new result.
     *
     * @param Result $result
     * @return \Michaeljennings\Laralastica\Result
     */
    protected function newResult(Result $result)
    {
        return new \Michaeljennings\Laralastica\Result($result->getHit());
    }

    /**
     * If the callback is set run it on the query and then return the query.
     *
     * @param AbstractQuery $query
     * @param callable|null $callback
     * @return AbstractQuery
     */
    protected function returnQuery(AbstractQuery $query, callable $callback = null)
    {
        if ($callback) {
            $callback($query);
        }

        return $query;
    }
}