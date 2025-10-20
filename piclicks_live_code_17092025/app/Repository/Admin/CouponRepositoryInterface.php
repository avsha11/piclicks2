<?php

namespace App\Repository\Admin;

interface CouponRepositoryInterface
{
    public function getAll();
    public function getSingle($where);
    public function update($where,$update);
}
