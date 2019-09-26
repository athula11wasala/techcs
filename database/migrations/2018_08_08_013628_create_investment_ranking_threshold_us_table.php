<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Seeder;
use App\Models\DataSet;
use Carbon\Carbon;

class CreateInvestmentRankingThresholdUsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if ( !Schema::connection ( 'mysql_external_intake' )->hasTable ( 'investment_ranking_threshold_us' ) ) {
            Schema::connection ( 'mysql_external_intake' )->create ( 'investment_ranking_threshold_us', function (Blueprint $table) {

                $table->increments ( 'id' );
                $table->string ( 'segment' );
                $table->float ( 'low_medium', 8, 2 )->default ( 0 );
                $table->float ( 'medium_high', 8, 2 )->default ( 0 );;
                $table->integer ( 'dataset_id' )->default ( 0 );
                $table->integer ( 'latest' )->default ( 0 );
                $table->timestamps ();
            } );
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection ( 'mysql_external_intake' )->dropIfExists ( 'investment_ranking_threshold_us' );
    }

    public function dataSetSeeder(){

        $objDataset = new DataSet();
        $objDataset->data_set = 7;
        $objDataset->description ='2018 first quarter';
        $objDataset->from =  Carbon::parse('2018-01-01');
        $objDataset->to = Carbon::parse('2018-03-31');
        $objDataset->created_at = '2018-03-01 18:30:00';
        $objDataset->updated_at = '2018-03-31 18:31:00';
        $objDataset->save();

        $objDataset = new DataSet();
        $objDataset->data_set = 7;
        $objDataset->description ='2018 second quarter';
        $objDataset->from =  Carbon::parse('2018-04-01');
        $objDataset->to = Carbon::parse('2018-06-30');
        $objDataset->created_at = '2018-03-01 18:30:00';
        $objDataset->updated_at = '2018-03-31 18:31:00';
        $objDataset->save();

        $objDataset = new DataSet();
        $objDataset->data_set = 7;
        $objDataset->description ='2018 third quarter';
        $objDataset->from =  Carbon::parse('2018-07-01');
        $objDataset->to = Carbon::parse('2018-09-30');
        $objDataset->created_at = '2018-03-01 18:30:00';
        $objDataset->updated_at = '2018-03-31 18:31:00';
        $objDataset->save();

    }

}







