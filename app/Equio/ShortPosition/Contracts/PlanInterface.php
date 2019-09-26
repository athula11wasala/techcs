<?php
namespace App\Equio\ShortPosition\Contracts;

interface PlanInterface 
{

    public function cancelPlan($companies = []);

    public function purchasePlan(
        $companies = [], $token = '', $cardId = '', $planId = ''
    );

    public function changeCompanies($companySymbols = []);

}