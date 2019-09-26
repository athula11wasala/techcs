<?php

namespace App\Repositories;

use Bosnadev\Repositories\Eloquent\Repository;

class TaxRatesGlossaryRepository extends Repository
{

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'App\Models\TaxRatesGlossary';
    }

    /**
     * Returns tax rates glossary
     */
    public function getTaxRatesGlossaryInfo()
    {
        $taxRatesGlossary = $this->model
            ->select('tax_rates_glossary.state','tax_rates_glossary.glossary_id','tax_glossary.glossary')
            ->join('tax_glossary', 'tax_rates_glossary.glossary_id', '=', 'tax_glossary.id')
            ->orWhere('state', 'CO')
            ->orWhere('state', 'WA')
            ->orWhere('state', 'OR')
            ->orWhere('state', 'CA')
            ->get();
        return $taxRatesGlossary;
    }

    /**
     * Returns tax glossary details
     */
    public function getTaxGlossaryInfo($state)
    {
        $taxGlossary = $this->model
            ->select('tax_rates_glossary.state','tax_glossary.glossary','tax_glossary.definition')
            ->join('tax_glossary', 'tax_rates_glossary.glossary_id', '=', 'tax_glossary.id')
            ->where('tax_rates_glossary.state', $state)
            ->get();
        \Log::info("==== TaxRatesGlossaryRepository->getTaxGlossaryInfo ", ['u' => json_encode($taxGlossary)]);
        return $taxGlossary;
    }

}
