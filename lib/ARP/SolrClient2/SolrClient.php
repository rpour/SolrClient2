<?php
namespace ARP\SolrClient2;

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

        /***********************************************************************
         * PREPAIR PAGING
         ***********************************************************************/
        $response->pages = (int)$this->params['rows'] > 0
            ? ceil((int)$response->count / (int)$this->params['rows'])
            : 0;

        if($response->pages >= 1) {
            $response->length = $this->pagingLength;
            $response->currentPage = ((int)$response->offset / (int)$this->params['rows']) + 1;

            // INDEX START
            if($response->currentPage > ($response->length / 2))
                $response->startPage = $response->currentPage - floor($response->length/2);
            else 
                $response->startPage = 1;
            
            // INDEX END
            if(($response->startPage + $response->length) > $response->pages && $response->pages > $response->length)
                $response->endPage = ceil($response->pages);
            else
                $response->endPage = $response->startPage + $response->length;

            // END OF LIST?
            if($response->endPage - $response->startPage < $response->length)
                $response->startPage = $response->startPage 
                    - ($response->length - ($response->endPage - $response->startPage));

            if($response->startPage < 1)
                $response->startPage = 1;
            
            if($response->currentPage < $response->pages)
                $response->nextPage = $response->currentPage + 1;

            if($response->currentPage > 1)
                $response->prevPage = $response->currentPage - 1;
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