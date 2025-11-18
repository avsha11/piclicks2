<?php

namespace App\Repository\Admin;

interface ShippingAmountRepositoryInterface
{
    public function getAll();
    public function getSingle($where);
    public function create($create);
    public function update($where,$update);
}
