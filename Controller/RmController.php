<?php
namespace App\Controller;

use RestApi\Controller\ApiController;

class RmController extends ApiController
{
    public function requestcount()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = $this->request->getData('rm_id');
            $options = array();
            $options['conditions']['rm_id'] = $rm_id;
            $totalRequests = $this->AmpGrievance->find('all', $options)->count();
            // pending request count
            $options['conditions']['rm_approval_status'] = "0";
            $pendingRequests = $this->AmpGrievance->find('all', $options)->count();
            // approved request count
            $options['conditions']['status'] = "Resolved";
            $approvedRequests = $this->AmpGrievance->find('all', $options)->count();
            // rejected request count
            $options['conditions']['status'] = "Rejected";
            $rejectedRequests = $this->AmpGrievance->find('all', $options)->count();
            $this->httpStatusCode = 200;
            $this->apiResponse['total'] = $totalRequests;
            $this->apiResponse['fulfilled'] = $approvedRequests;
            $this->apiResponse['pending'] = $pendingRequests;
            $this->apiResponse['rejected'] = $rejectedRequests;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getallrequests()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = 0;
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $options = array();
            if ($this->request->getData('rm_id') != "") {
                $rm_id = $this->request->getData('rm_id');
            }
            $options['conditions']['rm_id'] = $rm_id;
            $options['limit'] = $limit;
            $options['order'] = 'submitted_date DESC';
            $options['offset'] = $start;
            $requestList = $this->AmpGrievance->find('all', $options)->toArray();
            $this->httpStatusCode = 200;
            $this->apiResponse['requests'] = $requestList;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getpendingrequests()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = 0;
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $options = array();
            if ($this->request->getData('rm_id') != "") {
                $rm_id = $this->request->getData('rm_id');
            }
            $options['conditions']['rm_id'] = $rm_id;
            $options['conditions']['rm_approval_status'] = '0';
            $options['limit'] = $limit;
            $options['order'] = 'submitted_date DESC';
            $options['offset'] = $start;
            $requestList = $this->AmpGrievance->find('all', $options)->toArray();
            $this->httpStatusCode = 200;
            $this->apiResponse['requests'] = $requestList;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getapprovedrequests()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = 0;
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $options = array();
            if ($this->request->getData('rm_id') != "") {
                $rm_id = $this->request->getData('rm_id');
            }
            $options['conditions']['rm_id'] = $rm_id;
            $options['conditions']['status'] = 'Resolved';
            $options['limit'] = $limit;
            $options['order'] = 'submitted_date DESC';
            $options['offset'] = $start;
            $requestList = $this->AmpGrievance->find('all', $options)->toArray();
            $this->httpStatusCode = 200;
            $this->apiResponse['requests'] = $requestList;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }

    public function getrejectedrequests()
    {
        header("Access-Control-Allow-Origin: *");
        if ($this->checkToken()) {
            $rm_id = 0;
            $page = $this->request->getData('page');
            $limit = 10;
            $start = ($page - 1) * $limit;
            $options = array();
            if ($this->request->getData('rm_id') != "") {
                $rm_id = $this->request->getData('rm_id');
            }
            $options['conditions']['rm_id'] = $rm_id;
            $options['conditions']['status'] = 'Rejected';
            $options['limit'] = $limit;
            $options['order'] = 'submitted_date DESC';
            $options['offset'] = $start;
            $requestList = $this->AmpGrievance->find('all', $options)->toArray();
            $this->httpStatusCode = 200;
            $this->apiResponse['requests'] = $requestList;
        } else {
            $this->httpStatusCode = 403;
            $this->apiResponse['message'] = "your session has been expired";
        }
    }
}
