<?php

namespace App\Repository\Admin;

interface AdminRepositoryInterface
{
    public function checkLoginRepository();
    public function loginRepository($request);
    public function logoutRepository();
    // public function changePasswordRepository($request, $id);
    public function profileUpdateRepository($request);



}
