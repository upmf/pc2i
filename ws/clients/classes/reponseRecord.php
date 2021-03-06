<?php
/**
 * 
 * @package	MoodleWS
 * @copyright	(c) P.Pollet 2007 under GPL
 */
class reponseRecord {
	/** 
	* @var boolean
	*/
	public $bonne;
	/** 
	* @var string
	*/
	public $error;
	/** 
	* @var int
	*/
	public $id;
	/** 
	* @var int
	*/
	public $id_etab;
	/** 
	* @var int
	*/
	public $num;
	/** 
	* @var string
	*/
	public $qid;
	/** 
	* @var string
	*/
	public $reponse;

	/**
	* default constructor for class reponseRecord
	* @param boolean $bonne
	* @param string $error
	* @param int $id
	* @param int $id_etab
	* @param int $num
	* @param string $qid
	* @param string $reponse
	* @return reponseRecord
	*/
	 public function reponseRecord($bonne=false,$error='',$id=0,$id_etab=0,$num=0,$qid='',$reponse=''){
		 $this->bonne=$bonne   ;
		 $this->error=$error   ;
		 $this->id=$id   ;
		 $this->id_etab=$id_etab   ;
		 $this->num=$num   ;
		 $this->qid=$qid   ;
		 $this->reponse=$reponse   ;
	}
	/* get accessors */

	/**
	* @return boolean
	*/
	public function getBonne(){
		 return $this->bonne;
	}


	/**
	* @return string
	*/
	public function getError(){
		 return $this->error;
	}


	/**
	* @return int
	*/
	public function getId(){
		 return $this->id;
	}


	/**
	* @return int
	*/
	public function getId_etab(){
		 return $this->id_etab;
	}


	/**
	* @return int
	*/
	public function getNum(){
		 return $this->num;
	}


	/**
	* @return string
	*/
	public function getQid(){
		 return $this->qid;
	}


	/**
	* @return string
	*/
	public function getReponse(){
		 return $this->reponse;
	}

	/*set accessors */

	/**
	* @param boolean $bonne
	* @return void
	*/
	public function setBonne($bonne){
		$this->bonne=$bonne;
	}


	/**
	* @param string $error
	* @return void
	*/
	public function setError($error){
		$this->error=$error;
	}


	/**
	* @param int $id
	* @return void
	*/
	public function setId($id){
		$this->id=$id;
	}


	/**
	* @param int $id_etab
	* @return void
	*/
	public function setId_etab($id_etab){
		$this->id_etab=$id_etab;
	}


	/**
	* @param int $num
	* @return void
	*/
	public function setNum($num){
		$this->num=$num;
	}


	/**
	* @param string $qid
	* @return void
	*/
	public function setQid($qid){
		$this->qid=$qid;
	}


	/**
	* @param string $reponse
	* @return void
	*/
	public function setReponse($reponse){
		$this->reponse=$reponse;
	}

}

?>
