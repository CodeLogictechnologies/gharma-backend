<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;

class SessionController extends BaseController
{
    protected $userid;
    protected $orgid;

    public function __construct()
    {
        // Load session data into class properties
        $this->userid = Session::get('userid');
        $this->orgid  = Session::get('orgid');
    }

    /**
     * Set session for logged-in user
     */
    protected function setSession($user)
    {
        $this->userid = $user->id;
        $this->orgid  = $user->orgid;

        Session::put([
            'userid' => $this->userid,
            'orgid'  => $this->orgid,
            'email'  => $user->email,
            'name'   => $user->name,
        ]);
    }

    /**
     * Get current logged-in user's ID
     */
    protected function getUserId()
    {
        return $this->userid;
    }

    /**
     * Get current logged-in user's organization ID
     */
    protected function getOrgId()
    {
        return $this->orgid;
    }
}