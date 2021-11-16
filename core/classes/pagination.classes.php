<?php

/*
	* Korori-Gaming
	* Pagination Class Set
	* @Version 4.0.2
	* Developed by: Ami (亜美) Denault
*/
declare(strict_types=1);
class Pagination {
		
	public	$itemsPerPage,
			$currentPage,
			$total,
			$textNav,
			$get_type,
			$_link;
	
	private $_navigation,		
			$_itemHtml;
       
	public function __construct()
	{
		
		$this->itemsPerPage = 5;
		$this->currentPage  = 1;
		$this->total		= 0;
		$this->get_type 	= 'page';
		
		
		$this->_navigation  = array(
				'next'=>'&#10093;&#10093;',
				'pre' =>'&#10092;&#10092;'
		);			
		$this->_link 	  	= filter_var($_SERVER['PHP_SELF'], FILTER_SANITIZE_STRING);
		$this->_itemHtml  	= '';
	}
       
	public function generate()
	{
		$this->_itemHtml = $this->_getHTMLData();	
		return $this->_itemHtml;
	}

	public function setTotal(int $total){
		$this->total		= $total;
	}
	public function setCurrent(int $current){
		$this->currentPage		= $current;
	}

	public function setPerPage(int $itemsPerPage){
		$this->itemsPerPage		= $itemsPerPage;
	}

	public function setLinkText(string $linkText){
		$this->_link		= $linkText;
	}

	private function  _getHTMLData()
	{

		$layout = new Template("_pagination.tpl");
		$list_item = new Template("_pagination_item.tpl");
		$list ='';
		if($this->currentPage>1){
			$list_item->setArray(array('active'=>'','link'=>$this->_link .'/'.($this->currentPage-1).'-' .$this->get_type,'name'=>$this->_navigation['pre']));
			$list .= $list_item->show();
		}
		if($this->total > $this->itemsPerPage){
			$start = ($this->currentPage <= $this->itemsPerPage)?1:($this->currentPage - $this->itemsPerPage);
			$end   = (ceil($this->total/$this->itemsPerPage) - $this->currentPage >= $this->itemsPerPage)?($this->currentPage+$this->itemsPerPage): (ceil($this->total/$this->itemsPerPage));
		}else{
			$start = 1;
			$end   = ceil($this->total/$this->itemsPerPage);
		} 
	

		for($i = $start; $i <= $end; $i++){
			$list_item->setArray(array('active'=>($i==$this->currentPage?"active":''),'link'=>$this->_link .'/'.$i. '-' .$this->get_type,'name'=>$i));
			$list .= $list_item->show();
		}

		if($this->currentPage<$end){
			$list_item->setArray(array('active'=>'','link'=>$this->_link .'/'.($this->currentPage+1).'-' .$this->get_type,'name'=>$this->_navigation['next']));
			$list .= $list_item->show();
		}

		$layout->setArray(array('list'=>$list));
		return $layout->show();
	}
}
