<?php
/*
arr_multisort 1.1
Copyright: Left
---------------------------------------------------------------------------------
Version:        1.1
Date:           11 January 2005
---------------------------------------------------------------------------------
Author:        Alexander Minkovsky (a_minkovsky@hotmail.com)
---------------------------------------------------------------------------------
License:        Choose the more appropriated for You - I don't care.
---------------------------------------------------------------------------------
Description:
Class makes multicolumn sorting of associative arrays in format provided for example by mysql_fetch_assoc.
Column names to be used when sorting can be specified as well the sorting direction for each column.
Optional external to the class compare functions can be specified for each column.
Look below for requirements concerning external compare functions.
If external compare functions are not supplied, the internal _compare method is used.
Dates are sorted correctly using the internal compare if they comply with GNU date syntax
(http://www.gnu.org/software/tar/manual/html_chapter/tar_7.html)
---------------------------------------------------------------------------------
Example usage: see example.php
---------------------------------------------------------------------------------
*/

define("SRT_ASC",1);
define("SRT_DESC",-1);

Class arr_multisort{

  //The array to be sorted
  var $arr = NULL;

  /*
  Sorting definition
  Format: array (
                    array(
                            "colName"   =>   "some column name",
                            "colDir"    =>   SRT_ASC,
                            "compareFunc" => "myCompareFunction"
                         ),
                    ..........................
                )
  "compareFunc" element is optional - NULL by default and by default the class internal compare is used.
  If External compare function is supplied it must conform to the following requirements:
    ~ Accept two T_STRING parameters and return:
        0  - if parameters are equal
        1  - if first parameter is greater than second
        -1 - if second parameter is greater than first
  */
  var $sortDef = NULL;

  //Constructor
  function arr_multisort(){
    $this->arr = array();
    $this->sortDef = array();
  }

  //setArray method - sets the array to be sorted
  function setArray(&$arr){
    $this->arr = $arr;
  }

  /*
  addColumn method - ads entry to sorting definition
  If column exists, values are overwriten.
  */
  function addColumn($colName="",$colDir=SRT_ASC,$compareFunc=NULL){
    $idx = $this->_getColIdx($colName);
    if($idx < 0){
      $this->sortDef[] = array();
      $idx = count($this->sortDef)-1;
    }
    $this->sortDef[$idx]["colName"] = $colName;
    $this->sortDef[$idx]["colDir"] = $colDir;
    $this->sortDef[$idx]["compareFunc"] = $compareFunc;
  }

  //removeColumn method - removes entry from sorting definition
  function removeColumn($colName=""){
    $idx = $this->_getColIdx($colName);
    if($idx >= 0) array_splice($this->sortDef,$idx,1);
  }

  //resetColumns - removes any columns from sorting definition. Array to sort is not affected.
  function resetColumns(){
    $this->sortDef = array();
  }

  //sort() method
  function &sort(){
    usort($this->arr,array($this,"_compare"));
    return $this->arr;
  }

  //_getColIdx method [PRIVATE]
  function _getColIdx($colName){
    $idx = -1;
    for($i=0;$i<count($this->sortDef);$i++){
      $colDef = $this->sortDef[$i];
      if($colDef["colName"] == $colName) $idx = $i;
    }
    return $idx;
  }

  //Comparison function [PRIVATE]
  function _compare($a,$b,$idx = 0){
    if(count($this->sortDef) == 0) return 0;
    $colDef = $this->sortDef[$idx];
    $a_cmp = $a[$colDef["colName"]];
    $b_cmp = $b[$colDef["colName"]];
    if(is_null($colDef["compareFunc"])){
      $a_dt = strtotime($a_cmp);
      $b_dt = strtotime($b_cmp);
      if(($a_dt == -1) || ($b_dt == -1) || ($a_dt == false) || ($b_dt == false))
        $ret = $colDef["colDir"]*strnatcasecmp($a_cmp,$b_cmp);
      else{
        $ret = $colDef["colDir"]*(($a_dt > $b_dt)?1:(($a_dt < $b_dt)?-1:0));
      }
    }
    else{
      $code = '$ret = ' . $colDef["compareFunc"] . '("' . $a_cmp . '","' . $b_cmp . '");';
      eval($code);
      $ret = $colDef["colDir"]*$ret;
    }
    if($ret == 0){
      if($idx < (count($this->sortDef)-1))
        return $this->_compare($a,$b,$idx+1);
      else
        return $ret;
    }
    else
      return $ret;
  }

}
?>
