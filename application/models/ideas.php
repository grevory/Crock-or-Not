<?php
class Ideas extends CI_Model {

    var $title   = '';
    var $content = '';
    var $date    = '';

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    /**
     * Gets a random idea
     *
     * @exclude string Comma delimited ids from the idea table that should not be included
     */ 
    function get_random_idea($exclude)
    {        
        $id = false;
        do {
            $random_row = $this->random_row('ideas');
            $id = $this->is_good_idea($exclude,$random_row);
        } while (!$id);
        
        if (!$id)
            die('No id');
        
        $random_idea = $this->db->query('SELECT id,name
            FROM ideas
            WHERE id = '.$id);
        return $random_idea->row();
    }
    
    /**
     * Checks to makes sure this idea is good to use
     * @ids string Comma delimited string of idea ids to skip - ones that have been viewed before
     * @id int The id being compared to see if it is good
     */
    function is_good_idea ($ids,$id)
    {
        $ids_to_skip = explode(',',$ids);
        
        foreach ($ids_to_skip as $bad_id)
        {
            if ($id == $bad_id)
                return false;
        }
        
        return $id;
    }
    
    function get_vote_results($id)
    {
        $tally = array('crock'=>0,'not'=>0);
        
        $results = $this->db->query('SELECT crock, idea_id
            FROM votes
            WHERE idea_id = '.$id);
        
        if ($results->num_rows() > 0)
            foreach ($results->result() as $result)
            {
                if ($result->crock == 1)
                    $tally['crock']++;
                else
                    $tally['not']++;
            }
            
        return $tally;
    }
    
    function get_proof($id)
    {
        $all_proof = $this->db->query('SELECT url, crock, idea_id
            FROM proof
            WHERE idea_id = '.$id);
        
        if ($all_proof->num_rows() > 0)
        {
            $collection = array('crock'=>array(),'not'=>array());
            
            foreach ($all_proof->result() as $proof)
            {
                $key = 'not';
                if ($proof->crock == 1)
                    $key = 'crock';
                
                array_push($collection[$key],$proof->url);    
            }
            
            return $collection;
        }
        
        return false;
    }

    function insert_entry()
    {
        $this->title   = $_POST['title']; // please read the below note
        $this->content = $_POST['content'];
        $this->date    = time();

        $this->db->insert('entries', $this);
    }

    function update_entry()
    {
        $this->title   = $_POST['title'];
        $this->content = $_POST['content'];
        $this->date    = time();

        $this->db->update('entries', $this, array('id' => $_POST['id']));
    }
    
    /**
     * Returns a random row ID faster than MySQL can
     *
     * @table   The name of the MySQL table
     * @column  The name of the column being randomized
     *          Default: id
     */
    function random_row($table, $column="id")
    {
        $max_sql = "SELECT max(" . $column . ") 
        AS max_id
        FROM " . $table. "
        LIMIT 1";
        
        $max_row_query = $this->db->query($max_sql);
        $max_row = $max_row_query->row();
        
        $random_number = mt_rand(1, $max_row->max_id);
        
        $random_sql = "SELECT *
        FROM " . $table . "
        WHERE " . $column . " >= " . $random_number . " 
        ORDER BY " . $column . " ASC
        LIMIT 1";
        
        $random_row_query = $this->db->query($random_sql);
        
        if ($random_row_query->num_rows() < 1)
        {
            echo 'No results';
            $random_sql = "SELECT *
                FROM " . $table . "
                WHERE " . $column . " < " . $random_number . " 
                ORDER BY " . $column . " DESC
                LIMIT 1";
            
            $random_row_query = $this->db->query($random_sql);
        }
        $random_row = $random_row_query->row();
        
        return $random_row->id;
      
    }

}
?>