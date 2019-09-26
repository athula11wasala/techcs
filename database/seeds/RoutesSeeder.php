<?php

use App\Models\SubscriptonRoute;
use App\Models\Routes;
use Illuminate\Database\Seeder;

class RoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("route")->delete();

        $route = new Routes();
        $route->id = 1;
        $route->routes = "cannibalization";
        $route->save();

        $route = new Routes();
        $route->id = 2;
        $route->routes = "analyst-reports/interactive-reports";
        $route->save();

        $route = new Routes();
        $route->id = 3;
        $route->routes = "chart";
        $route->save();

        $route = new Routes();
        $route->id = 4;
        $route->routes = "chart/all-keywords";
        $route->save();

        $route = new Routes();
        $route->id = 5;
        $route->routes = "chart/all-charts-name";
        $route->save();


        $this->updateRouteSubscriptions();
    }

    public function updateRouteSubscriptions()
    {
        DB::table("subscription_route")->truncate();
        // ------------------ Route 1---------------
        // Essentials = 1
        $route = new SubscriptonRoute();
        $route->route_id = 1;
        $route->subscription_id = 1;
        $route->save();

        // Premium = 2
        $route = new SubscriptonRoute();
        $route->route_id = 1;
        $route->subscription_id = 2;
        $route->save();

        // Premium + = 3
        $route = new SubscriptonRoute();
        $route->route_id = 1;
        $route->subscription_id = 3;
        $route->save();

        // ------------------ Route 2---------------
        // Essentials = 1
        $route = new SubscriptonRoute();
        $route->route_id = 2;
        $route->subscription_id = 1;
        $route->save();

        // ------------------ Route 3---------------
        // Essentials = 1
        $route = new SubscriptonRoute();
        $route->route_id = 3;
        $route->subscription_id = 1;
        $route->save();

        // Premium = 2
        $route = new SubscriptonRoute();
        $route->route_id = 3;
        $route->subscription_id = 2;
        $route->save();

        // ------------------ Route 4---------------
        // Essentials = 1
        $route = new SubscriptonRoute();
        $route->route_id = 4;
        $route->subscription_id = 1;
        $route->save();

        // Premium = 2
        $route = new SubscriptonRoute();
        $route->route_id = 4;
        $route->subscription_id = 2;
        $route->save();

        // ------------------ Route 5---------------
        // Essentials = 1
        $route = new SubscriptonRoute();
        $route->route_id = 5;
        $route->subscription_id = 1;
        $route->save();

        // Premium = 2
        $route = new SubscriptonRoute();
        $route->route_id = 5;
        $route->subscription_id = 2;
        $route->save();
    }
}
