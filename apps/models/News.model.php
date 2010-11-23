<?php
class News extends Model
{
	//Here write Database table's name
	public $table = 'st_news';
	public $catelog_table ='st_catelog';
	public $pagesize=25;

	function getAllCatelog()
	{
		$cates = $this->db->query("select * from {$this->catelog_table}")->fetchall();
        $new_cates = array();
        foreach($cates as $ca)
        {
            $new_cates[$ca['id']] = $ca;
        }
        return $new_cates;
	}
	function getCatelog($cid)
	{
		return $this->db->query("select * from {$this->catelog_table} where id={$cid} limit 1")->fetch();
	}
}