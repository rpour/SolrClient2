<?php
namespace ARP\SolrClient2;

class SolrQuery extends SolrCore {
    protected $page = 1;

    /**
     * Constructor.
     * @param array $options Options.
     */
    public function __construct($options) {
        parent::__construct($options);
    }

    /**
     * Search function.
     * @param  string   $query  Search query.
     * @param  integer  $offset   Start offset.
     * @param  integer  $limit   Hits per page.
     * @param  array    $params Parameters
     * @return Object   Result  documents.
     */
    public function exec($query = null, $page = null, $hits = null, $params = array()) {
        $this->params = $this->mergeRecursive($this->params, $params);

        if(!is_null($hits))  $this->params['rows'] = (int)$hits;
        if(!empty($query)) $this->params['q'] = $query;
        if(!is_null($page))  $this->page($page);

        // calculate offset
        if($this->params['start'] === 0 && $this->page > 1)
            $this->offset(($this->page * $this->params['rows']) - $this->params['rows']);

        $response = $this->solrSelect($this->params);

        $content = json_decode($response->content);

        unset($response);

        /***********************************************************************
         * PREPAIR SOLR SEARCH RESULT
         ***********************************************************************/
        $result = new \stdClass();

        if(isset($content->response->numFound))
            $result->count = $content->response->numFound;

        if(isset($content->response->start))
            $result->offset = $content->response->start;

        if(isset($content->response->docs) && !empty($content->response->docs))
            $result->documents = $content->response->docs;

        if(isset($content->facet_counts->facet_fields)) {
            foreach($content->facet_counts->facet_fields as $key => $val) {
                if($this->autocompleteField === $key)
                    $result->autocomplete = $val;
                else
                    $result->facets[$key] = $val;
            }
        }

        return $result;
    }

    /**
     * Enable debug query
     * @param  boolean $debug
     */
    public function debug($debug) {
        if($debug)
            $this->params['deubgQuery'] = 'true';
        else if(isset($this->params['deubgQuery']))
            unset($this->params['deubgQuery']);

        return $this;
    }

    /**
     * Set result page
     * @param  integer $page Result page
     */
    public function page($page) {
        $this->page = (int)$page > 0 ? (int)$page : 1;
        return $this;
    }

    /**
     * Set start offset.
     * @param  integer $offset Start position.
     */
    public function offset($offset) {
        $this->params['start'] = (int)$offset;
        return $this;
    }

    /**
     * Set result limit.
     * @param  integer $limit Result limit.
     */
    public function limit($limit) {
        $this->params['rows'] = (int)$limit;
        return $this;
    }

    /**
     * Select result fields.
     * @param  string $select Fields
     */
    public function select($select) {
        $this->params['fl'] = $select;
        return $this;
    }

    public function where($key, $value) {
        if(is_array($value)) {
            $tmp = "";
            foreach($value as $val)
                $tmp .= ' OR ' . $key . ':"' . $this->escapePhrase($val) . '"';

            if($tmp !== "")
                $this->appendToFilter("AND (" . substr($tmp, 4) . ")");
        } else
            $this->appendToFilter('AND ' . $key . ':"' . $this->escapePhrase($value) . '"');

        return $this;
    }

    public function orderBy($sort, $direction = 'asc') {
        $this->params['sort'] = $sort . ' ' . $direction;
        return $this;
    }

    /**
     * Queryparser.
     * @param  string $queryParser Queryparser
     */
    public function queryParser($queryParser) {
        $this->params['defType'] = $queryParser;
        return $this;
    }

    /**
     * Faceting
     * @param  mixed  $fields   Fields
     * @param  integer $mincount Returns only fileds more than mincount.
     * @param  string  $sort     Fields.
     * http://wiki.apache.org/solr/SimpleFacetParameters
     */
    public function facet($fields, $mincount = 1, $sort = 'index') {
        if(is_string($fields))
            $fields = array($fields);

        $this->params['facet'] = 'on';
        $this->params['facet.field'] = $fields;
        $this->params['facet.mincount'] = $mincount;
        $this->params['facet.sort'] = $sort;
        return $this;
    }

    /**
     * Escape searchstring.
     * @param  string $string Searchstring.
     * @return string         Escaped searchstring.
     */
    public function escape($string) {
        return preg_replace(
            '/(\+|-|&&|\|\||!|\(|\)|\{|}|\[|]|\^|"|~|\*|\?|:|\\\)/',
            '\\\$1', 
            $string
        );
    }

    /**
     * Escape search phrase.
     * @param  string $string Phrase string.
     * @return string         Escaped phrase.
     */
    public function escapePhrase($string) {
        return preg_replace(
            '/("|\\\)/',
            '\\\$1', 
            $string
        );
    }
}