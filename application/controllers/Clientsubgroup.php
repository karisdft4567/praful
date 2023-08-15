<?php
/**
 * Geo POS -  Accounting,  Invoicing  and CRM Application
 * Copyright (c) Rajesh Dukiya. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */

defined('BASEPATH') or exit('No direct script access allowed');

class Clientsubgroup extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('clientgroup_model', 'clientgroup');
        $this->load->model('clientsubgroup_model', 'clientsubgroup');

        $this->load->model('customers_model', 'customers');
        $this->load->library("Aauth");
        if (!$this->aauth->is_loggedin()) {
            redirect('/user/', 'refresh');
        }
        if (!$this->aauth->premission(3)) {

            exit('<h3>Sorry! You have insufficient permissions to access this section</h3>');

        }
        $this->li_a = 'crm';
    }

    //groups
    public function index()
    { 
        $data['group'] = $this->customers->group_list();
        $data['subgroup'] = $this->customers->subgroup_list();

       // print_R($data['subgroup'] );die;
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Client Sub Groups';
        $head['clgroups_menu']=$this->customers->group_list();
        $head['clsubgroups_menu']=$this->customers->subgroup_list();

        

        $this->load->view('fixed/header', $head);
        $this->load->view('groups/subgroups', $data);
        $this->load->view('fixed/footer');
    }

    //view
    public function groupview()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $id = $this->input->get('id');
        $data['group'] = $this->clientgroup->details($id);

        $data['subgroup'] = $this->clientsubgroup->details($id);

        $head['title'] = 'Group View';
        $head['clgroups_menu']=$this->customers->group_list();
        $head['clsubgroups_menu']=$this->customers->subgroup_list();

        $this->load->view('fixed/header', $head);
        $this->load->view('groups/subgroupview', $data);
        $this->load->view('fixed/footer');
    }

    public function subgroupdetails()
    {   
         $id = $this->input->post('gid');
        $details= $this->clientsubgroup->dropdowndetails($id);

        echo json_encode($details);
    }

    //datatable
    public function grouplist()

    { 
        $id = $this->input->get('id');
        $list = $this->customers->get_datatables_subgroups($id);
        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $customers) {
            $no++;

            $row = array();
            $row[] = $no;
            $row[] = '<span class="avatar-sm align-baseline"><img class="rounded-circle"  src="' . base_url() . 'userfiles/customers/thumbnail/' . $customers->picture . '" ></span> &nbsp;<a href="customers/view?id=' . $customers->id . '">' . $customers->name . '</a>';
            $row[] = $customers->address . ',' . $customers->city . ',' . $customers->country;
            $row[] = $customers->email;
            $row[] = $customers->phone;
            $row[] = '<a href="' . base_url() . 'customers/view?id=' . $customers->id . '" class="btn btn-info btn-sm"><span class="fa fa-eye"></span>  ' . $this->lang->line('View') . '</a> <a href="' . base_url() . 'customers/edit?id=' . $customers->id . '" class="btn btn-success btn-sm"><span class="icon-pencil"></span> ' . $this->lang->line('Edit') . '</a> <a href="#" data-object-id="' . $customers->id . '" class="btn btn-danger btn-sm delete-object"><span class="fa fa-trash"></span></a>';
            $data[] = $row;
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->customers->count_all($id),
            "recordsFiltered" => $this->customers->count_filtered($id),
            "data" => $data,
        );
        //output to json format
        echo json_encode($output);
    }

    public function create()
    {
        $head['usernm'] = $this->aauth->get_user()->username;
        $head['title'] = 'Create Group';
        $head['clgroups_menu']=$this->customers->group_list();
        $head['clsubgroups_menu']=$this->customers->subgroup_list();
        $head['clsubgroups_menu']=$this->customers->subgroup_list();



        $this->load->view('fixed/header', $head);
        $this->load->view('groups/sub_add',$head);
        $this->load->view('fixed/footer');
    }

    public function add()
    {

        $parent_id = $this->input->post('customergroup', true);
        $sub_group_name = $this->input->post('group_name', true);
        $group_desc = $this->input->post('group_desc', true);
        $loc=$this->aauth->get_user()->loc;
        

        if ($parent_id) { 
            $this->clientsubgroup->add($parent_id,$sub_group_name, $group_desc,$loc);
        }
    }

    public function editgroup()
    {
        $gid = $this->input->get('id');
        $this->db->select('*');
        $this->db->from('geopos_cust_subgroup');
        $this->db->where('id', $gid);
        $query = $this->db->get();
        $data['subgroup'] = $query->row_array();
        $head['usernm'] = $this->aauth->get_user()->username;
        $data['customergroup'] = $this->customers->group_info($this->input->get('gid'));
        $data['customergrouplist'] = $this->customers->group_list();
        $head['title'] = 'Edit Group';
        $head['clgroups_menu']=$this->customers->group_list();
        $head['clsubgroups_menu']=$this->customers->subgroup_list();
        $head['clsubgroups_menu']=$this->customers->subgroup_list();



        $this->load->view('fixed/header', $head);
        $this->load->view('groups/subgroupedit', $data);
        $this->load->view('fixed/footer');

    }

    public function editgroupupdate()
    {
        $gid = $this->input->post('id', true);
        $parent_id = $this->input->post('customergroup', true);
        $group_name = $this->input->post('group_name', true);
        $group_desc = $this->input->post('group_desc', true);
        if ($gid) {
            $this->clientsubgroup->editgroupupdate($gid,$parent_id, $group_name, $group_desc);
        }
    }

    public function delete_i()
    { 
        if ($this->aauth->premission(11)) {
              $id = $this->input->post('deleteid');
            if ($id != 1) {
                $this->db->delete('geopos_cust_subgroup', array('id' => $id));
               // $this->db->set(array('sgid' => 1));
                //$this->db->where('sgid', $id);
                //$this->db->update('geopos_customers');
                echo json_encode(array('status' => 'Success', 'message' => $this->lang->line('DELETED')));
            } else if ($id == 1) {
                echo json_encode(array('status' => 'Error', 'message' => 'You can not delete the default group!'));
            } else {
                echo json_encode(array('status' => 'Error', 'message' => $this->lang->line('ERROR')));
            }
        } else {
            echo json_encode(array('status' => 'Error', 'message' =>
                $this->lang->line('ERROR')));
        }
    }

    function sendGroup()
    {
        $id = $this->input->post('gid');
        $subject = $this->input->post('subject', true);
        $message = $this->input->post('text');
        $attachmenttrue = false;
        $attachment = '';
        $recipients = $this->clientgroup->recipients($id);
        $this->load->model('communication_model');
        $this->communication_model->group_email($recipients, $subject, $message, $attachmenttrue, $attachment);
    }

    public function discount_update()
    {
        $gid = $this->input->post('gid', true);
        $disc_rate = (float)$this->input->post('disc_rate', true);

        if ($gid) {
            $this->clientgroup->editgroupdiscountupdate($gid, $disc_rate);
        }
    }
}