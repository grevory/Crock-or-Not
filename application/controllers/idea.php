<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Idea extends CI_Controller {

    public function __construct()
    {
         parent::__construct();
         //$this->load->helper('cookie');
         //$this->load->model('Ideas');
    }
    
    public function index()
    {
        $data = array();
        
        
        $exclude_ideas = get_cookie('Voted');
        
        $idea = $this->Ideas->get_random_idea($exclude_ideas);
        $results = $this->Ideas->get_vote_results($idea->id);
        $proof = $this->Ideas->get_proof($idea->id);
        
        if (strlen($exclude_ideas) > 0)
        {
            $exclude_ideas .= ','.$idea->id;
            // Start over    
            if ($this->_is_voted_out($exclude_ideas))
            {
                delete_cookie('Voted');
                $exclude_ideas = $idea->id;
            }
        }
        
        if (!$exclude_ideas)
            $exclude_ideas = $idea->id;
        
        set_cookie('Voted',$exclude_ideas,time()+3600);
        
        $data = array(
            'idea' => $idea,
            'results' => $results,
            'proof' => $proof
        );
        
        $this->load->view('home',$data);
    }
    
    public function get_next_idea ()
    {
        
    }
    
    public function vote ()
    {
        if ($this->input->is_ajax_request())
        {
            
            $idea_id = $this->input->post('id');
            $crock = $this->input->post('crock');
            $ip = $this->input->ip_address();
            
            if (!!$ip)
                return _json('Your IP is suspicious. We cannot process your request.');
            
            if (!$idea_id OR !$crock)
                return _json('Data is missing. Your request could not be processed.');
            
            if ( ! is_int($idea_id))
                return _json('The ID submitted is not of the correct format.');
            
            if ( ! ($crock == 1 OR $crock == 0) )
                return _json('That was not an option. Your only options are "Crock" or "Not".');
                
            $vote = array(
                'idea_id' => $idea_id,
                'crock' => $crock,
                'ip' => $ip,
                'date_voted' => date('Y-m-d H:i:s')
            );
            
            $vote_inserted = $this->db->insert_string('votes',$vote);
            
            if ($vote_inserted)
            {
                // Write code to handle what happens when the vote was sucessfully updated
                _json(array('success'=>true));
            }
        }
        return _json('The submission was not AJAX.');
    }
    
    private function _is_voted_out ($voted_ids = false)
    {
        if (!$voted_ids)
            $voted_ids = get_cookie('Voted');
        $total_votes = count(explode(',',$voted_ids));
        
        $ideas = $this->db->get('ideas');
        $total_ideas = $ideas->num_rows();
        
        if ($total_votes >= $total_ideas)
            return true;
        
        return false;
    }
}