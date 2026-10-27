<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */


/** 页面路由 */
$routes->match(['GET', 'POST'], 'login', 'Login::index');
$routes->get('login/out', 'Login::out');
$routes->get('test', 'Test::index');
$routes->get('/', 'Uri::index');
$routes->get('Uri/(:any)', 'Uri::$1');

/** 控制器路由 */
// Api
$routes->match(['GET', 'POST'], 'api/spiderbid/test', 'Api\SpiderBid::test');

$routes->match(['GET', 'POST'], 'api/App/(:any)', 'Api\App::$1');
$routes->match(['GET', 'POST'], 'Api/App/(:any)', 'Api\App::$1');
$routes->match(['GET', 'POST'], 'Api/SpiderBid/(:any)', 'Api\SpiderBid::$1');
$routes->match(['GET', 'POST'], 'Api/SpiderOrder/(:any)', 'Api\SpiderOrder::$1');
// Base
$routes->match(['GET', 'POST'], 'Base/Applicant/(:any)', 'Base\Applicant::$1');
$routes->match(['GET', 'POST'], 'Base/Areas/(:any)', 'Base\Areas::$1');
$routes->match(['GET', 'POST'], 'Base/AttendancePlan/(:any)', 'Base\AttendancePlan::$1');
$routes->match(['GET', 'POST'], 'Base/Bid/(:any)', 'Base\Bid::$1');
$routes->match(['GET', 'POST'], 'Base/Billfrom/(:any)', 'Base\Billfrom::$1');
$routes->match(['GET', 'POST'], 'Base/CameraView/(:any)', 'Base\CameraView::$1');
$routes->match(['GET', 'POST'], 'Base/Category/(:any)', 'Base\Category::$1');
$routes->match(['GET', 'POST'], 'Base/Clockin/(:any)', 'Base\Clockin::$1');
$routes->match(['GET', 'POST'], 'Base/Company/(:any)', 'Base\Company::$1');
$routes->match(['GET', 'POST'], 'Base/CompanyAccount/(:any)', 'Base\CompanyAccount::$1');
$routes->match(['GET', 'POST'], 'Base/CompetitiveMonitor/(:any)', 'Base\CompetitiveMonitor::$1');
$routes->match(['GET', 'POST'], 'Base/Contact/(:any)', 'Base\Contact::$1');
$routes->match(['GET', 'POST'], 'Base/DeliverBag/(:any)', 'Base\DeliverBag::$1');
$routes->match(['GET', 'POST'], 'Base/DeliverBagOff/(:any)', 'Base\DeliverBagOff::$1');
$routes->match(['GET', 'POST'], 'Base/DeliverBagRec/(:any)', 'Base\DeliverBagRec::$1');
$routes->match(['GET', 'POST'], 'Base/DeliverCompany/(:any)', 'Base\DeliverCompany::$1');
$routes->match(['GET', 'POST'], 'Base/DiscountReq/(:any)', 'Base\DiscountReq::$1');
$routes->match(['GET', 'POST'], 'Base/Duty/(:any)', 'Base\Duty::$1');
$routes->match(['GET', 'POST'], 'Base/Goods/(:any)', 'Base\Goods::$1');
$routes->match(['GET', 'POST'], 'Base/GoodsDevReq/(:any)', 'Base\GoodsDevReq::$1');
$routes->match(['GET', 'POST'], 'Base/GoodsTask/(:any)', 'Base\GoodsTask::$1');
$routes->match(['GET', 'POST'], 'Base/Home/(:any)', 'Base\Home::$1');
$routes->match(['GET', 'POST'], 'Base/Invoice/(:any)', 'Base\Invoice::$1');
$routes->match(['GET', 'POST'], 'Base/Log/(:any)', 'Base\Log::$1');
$routes->match(['GET', 'POST'], 'Base/ManagePlan/(:any)', 'Base\ManagePlan::$1');
$routes->match(['GET', 'POST'], 'Base/Netdisk/(:any)', 'Base\Netdisk::$1');
$routes->match(['GET', 'POST'], 'Base/News/(:any)', 'Base\News::$1');
$routes->match(['GET', 'POST'], 'Base/Note/(:any)', 'Base\Note::$1');
$routes->match(['GET', 'POST'], 'Base/Option/(:any)', 'Base\Option::$1');
$routes->match(['GET', 'POST'], 'Base/PayReq/(:any)', 'Base\PayReq::$1');
$routes->match(['GET', 'POST'], 'Base/Plan/(:any)', 'Base\Plan::$1');
$routes->match(['GET', 'POST'], 'Base/PotentialCustomer/(:any)', 'Base\PotentialCustomer::$1');
$routes->match(['GET', 'POST'], 'Base/PrintTemplate/(:any)', 'Base\PrintTemplate::$1');
$routes->match(['GET', 'POST'], 'Base/ProduceGroup/(:any)', 'Base\ProduceGroup::$1');
$routes->match(['GET', 'POST'], 'Base/ProKittingReq/(:any)', 'Base\ProKittingReq::$1');
$routes->match(['GET', 'POST'], 'Base/ProKittingTask/(:any)', 'Base\ProKittingTask::$1');
$routes->match(['GET', 'POST'], 'Base/ProProcessingProject/(:any)', 'Base\ProProcessingProject::$1');
$routes->match(['GET', 'POST'], 'Base/ProProcessingReq/(:any)', 'Base\ProProcessingReq::$1');
$routes->match(['GET', 'POST'], 'Base/ProProcessingTask/(:any)', 'Base\ProProcessingTask::$1');
$routes->match(['GET', 'POST'], 'Base/PurQC/(:any)', 'Base\PurQC::$1');
$routes->match(['GET', 'POST'], 'Base/PurReq/(:any)', 'Base\PurReq::$1');
$routes->match(['GET', 'POST'], 'Base/PurSuggest/(:any)', 'Base\PurSuggest::$1');
$routes->match(['GET', 'POST'], 'Base/QuoteReq/(:any)', 'Base\QuoteReq::$1');
$routes->match(['GET', 'POST'], 'Base/ReviewHold/(:any)', 'Base\ReviewHold::$1');
$routes->match(['GET', 'POST'], 'Base/Right/(:any)', 'Base\Right::$1');
$routes->match(['GET', 'POST'], 'Base/SalerDuty/(:any)', 'Base\SalerDuty::$1');
$routes->match(['GET', 'POST'], 'Base/SalesOrderSpiderErrGoods/(:any)', 'Base\SalesOrderSpiderErrGoods::$1');
$routes->match(['GET', 'POST'], 'Base/SalesOrderSpiderErrPrice/(:any)', 'Base\SalesOrderSpiderErrPrice::$1');
$routes->match(['GET', 'POST'], 'Base/SalesTarget/(:any)', 'Base\SalesTarget::$1');
$routes->match(['GET', 'POST'], 'Base/Service/(:any)', 'Base\Service::$1');
$routes->match(['GET', 'POST'], 'Base/ShopAccount/(:any)', 'Base\ShopAccount::$1');
$routes->match(['GET', 'POST'], 'Base/Storage/(:any)', 'Base\Storage::$1');
$routes->match(['GET', 'POST'], 'Base/Task/(:any)', 'Base\Task::$1');
$routes->match(['GET', 'POST'], 'Base/Tool/(:any)', 'Base\Tool::$1');
$routes->match(['GET', 'POST'], 'Base/User/(:any)', 'Base\User::$1');
$routes->match(['GET', 'POST'], 'Base/WorkHour/(:any)', 'Base\WorkHour::$1');
$routes->match(['GET', 'POST'], 'Base/WorkRecord/(:any)', 'Base\WorkRecord::$1');
$routes->match(['GET', 'POST'], 'Base/WorkSite/(:any)', 'Base\WorkSite::$1');
// Bill
$routes->match(['GET', 'POST'], 'Bill/Achievement/(:any)', 'Bill\Achievement::$1');
$routes->match(['GET', 'POST'], 'Bill/Attendance/(:any)', 'Bill\Attendance::$1');
$routes->match(['GET', 'POST'], 'Bill/Deliver/(:any)', 'Bill\Deliver::$1');
$routes->match(['GET', 'POST'], 'Bill/Evaluate/(:any)', 'Bill\Evaluate::$1');
$routes->match(['GET', 'POST'], 'Bill/InvBq/(:any)', 'Bill\InvBq::$1');
$routes->match(['GET', 'POST'], 'Bill/InvIo/(:any)', 'Bill\InvIo::$1');
$routes->match(['GET', 'POST'], 'Bill/InvPc/(:any)', 'Bill\InvPc::$1');
$routes->match(['GET', 'POST'], 'Bill/InvPo/(:any)', 'Bill\InvPo::$1');
$routes->match(['GET', 'POST'], 'Bill/InvPu/(:any)', 'Bill\InvPu::$1');
$routes->match(['GET', 'POST'], 'Bill/InvSa/(:any)', 'Bill\InvSa::$1');
$routes->match(['GET', 'POST'], 'Bill/InvSc/(:any)', 'Bill\InvSc::$1');
$routes->match(['GET', 'POST'], 'Bill/InvSo/(:any)', 'Bill\InvSo::$1');
$routes->match(['GET', 'POST'], 'Bill/MonIo/(:any)', 'Bill\MonIo::$1');
$routes->match(['GET', 'POST'], 'Bill/OnlineshopMp/(:any)', 'Bill\OnlineshopMp::$1');
$routes->match(['GET', 'POST'], 'Bill/Report/(:any)', 'Bill\Report::$1');
// ueditor
$routes->match(['GET', 'POST'], 'ueditor/Ueditor', 'Ueditor\Ueditor::index');



