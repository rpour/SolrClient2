<?php
namespace ARP\SolrClient2;

/**
 * SolrClient
 * @author A.R.Pour
 */
class SolrClient extends SolrQuery {
    protected $pagingLength = 10;
    protected $wordWildcard = true;
    protected $numericWildcard = false;
    protected $leftWildcard = false;
    protected $wildcardMinStrlen = 3;
    protected $searchTerms = array();
    protected $autocompleteField = '';
    protected $autocompleteLimit = 10;
    protected $autocompleteSort = 'count';

    public function __construct($options = null) {
        parent::__construct($options);
    }

    public function pagingLength($length) {
        $this->pagingLength = (int)$length;
        return $this;
    }

    public function wordWildcard($wordWildcard) {
        $this->wordWildcard = (boolean)$wordWildcard;
        return $this;
    }

    public function numericWildcard($numericWildcard) {
        $this->numericWildcard = (boolean)$numericWildcard;
        return $this;
    }

    public function leftWildcard($leftWildcard) {
        $this->leftWildcard = (boolean)$leftWildcard;
        return $this;
    }

    public function wildcardMinStrlen($wildcardMinStrlen) {
        $this->wildcardMinStrlen = (int)$wildcardMinStrlen;
        return $this;
    }

    public function autocomplete($field, $limit = 10, $sort = 'count') {
        $this->autocompleteField = trim($field);
        $this->autocompleteLimit = (int)$limit;
        $this->autocompleteSort = $sort;
        return $this;
    }

    public function find($string = '') {
        $this->searchTerms = array_filter(explode(' ', $string));

        if($this->autocompleteField !== '') {
            $this->params['facet'] = 'on';

            if(!isset($this->params['facet.field']))
                $this->params['facet.field'] = array($this->autocompleteField);
            else
                $this->params['facet.field'][] = $this->autocompleteField;

            $this->params['f.' . $this->autocompleteField . '.facet.sort'] = $this->autocompleteSort;
            $this->params['f.' . $this->autocompleteField . '.facet.limit'] = $this->autocompleteLimit;
            $this->params['f.' . $this->autocompleteField . '.facet.prefix'] = end($this->searchTerms);
            $this->params['f.' . $this->autocompleteField . '.facet.mincount'] = 1;
        }

        $response = $this->exec($this->buildQuery('forQueryString'));

        // PREPAIR PAGING
        if(isset($response->count) && isset($response->offset)) {
            $paging = new Paging($response->count, $this->params['rows'], null, $response->offset);

            foreach($paging->calculate() as $key => $val)
                $response->$key = $val;
        }

        return $response;
    }

    private function buildQuery($method, $terms = null) {
        if(is_null($terms))
            $terms = $this->searchTerms;

        if(count($terms) !== 0) {
            array_walk($terms, 'self::' . $method);
            return implode(' ', $terms);            
        }

        return null;
    }

    private function forQueryString(&$term) {
        $term = trim($term);

        if((is_numeric($term) && $this->numericWildcard) || 
          (!is_numeric($term) && $this->wordWildcard) &&
          strlen($term) >= $this->wildcardMinStrlen) {
            
            if($this->leftWildcard)
                $term = '*' . $this->escape($term) . '*';
            else
                $term =  $this->escape($term) . '*';
        }
    }
}