<?php


namespace SWindon\XmlToArray;

use DOMDocument;

class XmlToArray {

    private $NAME_ATTRIBUTES = '@attributes';

    private $NAME_CONTENT = '@value';

    private $xml;

    /**
     * Convert a given XML String to Array
     *
     * @param string $xmlString
     * @return array|boolean false for failure
     */
    public function parse($xmlString) {
        $doc = new DOMDocument();
        $load = $doc->loadXML($xmlString);
        if ($load == false) {
            return false;
        }
        $this->xml = $doc->documentElement;
        return $this;
    }

    /**
     * Convert a given XML String to Array
     *
     * @param string $xmlString
     * @return array|boolean false for failure
     */
    public function toArray($extend=false) {
        if(!$root = $this->xml) {
            throw new Exception('Cannot parse an Array on NULL',1);
        }
        $output = [
            $root->tagName => $this->DOMDocumentToArray($root,$extend),
        ];
        return $output;
    }
    
    /**
     * Convert a given XML String to Array
     *
     * @param string $xmlString
     * @return array|boolean false for failure
     */
    public function dom() {
        if(!$root = $this->xml) {
            throw new Exception('Cannot parse Dom on NULL',1);
        }
        return $root;
    }

    /**
     * Convert DOMDocument->documentElement to array
     *
     * @param DOMElement $documentElement
     * @return array
     */
    protected function DOMDocumentToArray($documentElement,$extend=false) {
        $return = array();
        switch ($documentElement->nodeType) {

            case XML_CDATA_SECTION_NODE:
                $return = trim($documentElement->textContent);
                break;
            case XML_TEXT_NODE:
                $return = trim($documentElement->textContent);
                break;

            case XML_ELEMENT_NODE:
                for ($count=0, $childNodeLength=$documentElement->childNodes->length; $count<$childNodeLength; $count++) {
                    $child = $documentElement->childNodes->item($count);
                    $childValue = $this->DOMDocumentToArray($child,$extend);
                    if(isset($child->tagName)) {
                        $tagName = $child->tagName;
                        if(!isset($return[$tagName])) {
                            $return[$tagName] = array();
                        }
                        $return[$tagName][] = $childValue;
                    }
                    elseif($childValue || $childValue === '0') {
                        $return = (string) $childValue;
                    }
                }
                if($extend && $documentElement->attributes->length && !is_array($return)) {
                    $return = array($this->NAME_CONTENT=>$return);
                }

                if(is_array($return))
                {
                    if($extend && $documentElement->attributes->length)
                    {
                        $attributes = array();
                        foreach($documentElement->attributes as $attrName => $attrNode)
                        {
                            $attributes[$attrName] = (string) $attrNode->value;
                        }
                        $return[$this->NAME_ATTRIBUTES] = $attributes;
                    }
                    foreach ($return as $key => $value)
                    {
                        if(is_array($value) && count($value)==1 && $key!=$this->NAME_ATTRIBUTES)
                        {
                            $return[$key] = $value[0];
                        }
                    }
                }
                break;
        }
        if(empty($return)) $return = null;
        return $return;
    }

}
