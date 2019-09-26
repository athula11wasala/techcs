<?php

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermisssionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table("permissions")->delete();

        $dashBoard = new Permission();
        $dashBoard->name = "DASHBOARD";
        $dashBoard->display_name = "Dashboard";
        $dashBoard->description = "View Dashboard";
        $dashBoard->save();

        $reports_charts = new Permission();
        $reports_charts->name = "REPORTS_CHARTS";
        $reports_charts->display_name = "Reports & Charts";
        $reports_charts->description = "Reports & Charts";
        $reports_charts->save();

        $investing = new Permission();
        $investing->name = "INVESTING";
        $investing->display_name = "Investing";
        $investing->description = "View Investing";
        $investing->save();

        $researching = new Permission();
        $researching->name = "RESEARCHING";
        $researching->display_name = "Researching";
        $researching->description = "View Researching";
        $researching->save();

        $operation = new Permission();
        $operation->name = "OPERATION";
        $operation->display_name = "Operation";
        $operation->description = "View Operation";
        $operation->save();

        $referncelibuary = new Permission();
        $referncelibuary->name = "REFERENCE_LIBRARY";
        $referncelibuary->display_name = "Reference Library";
        $referncelibuary->description = "View Reference Library";
        $referncelibuary->save();


        $hemp = new Permission();
        $hemp->name = "HEMP_COMINGSOON";
        $hemp->display_name = "Hemp (coming soon)";
        $hemp->description = "View Hemp (coming soon)";
        $hemp->save();

        $faq = new Permission();
        $faq->name = "FAQ_HELP";
        $faq->display_name = "FAQ/Help";
        $faq->description = "View FAQ/Help";
        $faq->save();

        $globalcoming = new Permission();
        $globalcoming->name = "GLOBAL_COMINGSOON";
        $globalcoming->display_name = "Global (coming soon)";
        $globalcoming->description = "View Global (coming soon)";
        $globalcoming->save();

        $contactus = new Permission();
        $contactus->name = "CONTACTUS";
        $contactus->display_name = "Contact Us";
        $contactus->description = "View Contact Us";
        $contactus->save();

        $aboutUs = new Permission();
        $aboutUs->name = "ABOUTUS";
        $aboutUs->display_name = "About Us";
        $aboutUs->description = "View About Us";
        $aboutUs->save();

        $aboutUs = new Permission();
        $aboutUs->name = "REQUEST_TIME_WITH_ANINDUSTRY_ANALYST";
        $aboutUs->display_name = "Request Time With An Industry Analyst";
        $aboutUs->description = "Request Time With An Industry Analyst";
        $aboutUs->save();


        $userAdminTool = new Permission();
        $userAdminTool->name = "USERADMINTOOL";
        $userAdminTool->display_name = "User Admin Tool";
        $userAdminTool->description = "View User Admin Tool";
        $userAdminTool->save();

        $contactmgtment = new Permission();
        $contactmgtment->name = "CONTACTMANAGEMENT";
        $contactmgtment->display_name = "Contact Management";
        $contactmgtment->description = "View Contact Management";
        $contactmgtment->save();

        $adminReports = new Permission();
        $adminReports->name = "ADMINREPORTS";
        $adminReports->display_name = "Admin Reports";
        $adminReports->description = "View Admin Reports";
        $adminReports->save();

        $home = new Permission();
        $home->name = "HOME";
        $home->display_name = "Home";
        $home->description = "View Home";
        $home->save();

        $adminTool = new Permission();
        $adminTool->name = "ADMINTOOLS";
        $adminTool->display_name = "Admin Tools";
        $adminTool->description = "View Admin Tools";
        $adminTool->save();

    }
}
