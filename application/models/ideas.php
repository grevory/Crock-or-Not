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
        
        // I'm sure this can be handled with more grace
        if (!$id)
            die('No id');
        
        $random_idea = $this->db->query('SELECT id,name
            FROM ideas
            WHERE id = '.$id);
        return $random_idea->row();
    }
    
    /**
     * Checks to makes sure this idea has not been viewed before
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
    
    /** Get a full tally of the number of crock votes and not votes given an idea id
     * @id int The id of the idea you want the results for
     */
    function get_vote_results($id)
    {
        $tally = array('crock'=>0,'not'=>0);
        
        $results = $this->db->query('SELECT crock, idea_id
            FROM votes
            WHERE idea_id = '.$id);
        
        if ($results->num_rows() > 0)
            foreach ($results->result() as $result)
            {
                // Increment the tally for this vote
                $tally[$this->_get_vote_type]++;
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
                $vote = $this->_get_vote_type($proof->crock);
                
                array_push($collection[$vote],$proof->url);    
            }
            
            return $collection;
        }
        
        return false;
    }
    
    /**
     * Determines whether a vote was a crock or not
     * @vote int 1 represents a crock and 0 represents a not
     */
    function _get_vote_type ($vote)
    {
        if ($vote == 1)
            return 'crock';
        return 'not';
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