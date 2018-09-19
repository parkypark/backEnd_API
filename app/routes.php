<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

URL::forceRootUrl('https://api.starlinewindows.com');

Response::macro('prettyjson', function ($value, $status = 200) {
    return Response::make(json_encode($value, JSON_PRETTY_PRINT), $status)->header('Content-Type', 'application/json');
});

function get_user_info()
{
    return [];
}

Route::get('/', ['as' => 'home', function () {
    // Show API
    return View::make('showapi', array(
        'auth_info' => array('CN' => ''),
        'api_name' => 'Starline Architectural Windows',
        'api_routes' => [
            [
                'name' => '/production/v1',
                'description' => 'Production API v1.0',
                'url' => 'production/v1/'
            ],
            [
                'name' => '/quality/v1',
                'description' => 'Quality Assurance API v1.0',
                'url' => 'quality/v1/'
            ]
        ]
    ));
}]);

Route::group(['prefix' => 'auth'], function () {
    /*Route::get('login', [
        'as'	=> 'login.show',
        'uses'	=> 'LoginController@showLogin'
    ]);

    Route::post('login', [
        'as'	=> 'login.post',
        'uses'	=> 'LoginController@postLogin'
    ]);

    Route::get('logout', [
        'as'	=> 'logout',
        'uses'	=> 'LoginController@logout'
    ]);*/

    Route::post('access_token', [
        'as'	=> 'access_token',
        'uses'	=> 'LoginController@postAccessToken'
    ]);

    Route::post('refresh_access_token', [
        'as'	=> 'refresh_access_token',
        'uses'	=> 'LoginController@postRefreshAccessToken'
    ]);

    Route::get('authorizations', [
        'as'		=> 'authorizations',
        'before'	=> 'jwt-auth',
        'uses' 		=> 'LoginController@showAuthorizations'
    ]);

    Route::get('user', 'LoginController@showUser');

    Route::get('group-membership', 'LoginController@showGroupMembership');
});

Route::get('/test', function () {
    return Response::prettyjson(true);
});

// Lookup API

Route::group(['prefix' => 'lookup/v1'], function () {
    Route::get('glass-types', 'WindowMaker\WMMfgController@glassTypes');
});

// Production API
Route::group(['prefix' => 'production/v1'], function () {
    Route::get('/', function () {
        // Show API
        return View::make('showapi', array(
            'auth_info' => get_user_info(),
            'api_name' => 'Production',
            'api_routes' => [
                [
                    'name' => '/scan_logs',
                    'description' => 'Scan Logs API',
                    'url' => '/production/v1/scan_logs'
                ]
            ]
        ));
    });

    Route::get('employee/{employee_id}', 'TimeClock\Controller\TimeClockController@getEmployee');

    Route::get('scanner-version', function () {
        return '2.24';
    });

    Route::post('scan-log', 'ProductionWeb\PickListController@writeScanLog');

    Route::resource('order-status', 'ProductionWeb\OrderStatusController');
    Route::resource('vinyl-order-status', 'ProductionWeb\VinylOrderStatusController');

    Route::get('production-counters', 'ProductionWeb\OrderStatusController@getProductionCounters');

    Route::group(['prefix' => 'pick-list'], function () {
        Route::get('/', 'ProductionWeb\PickListController@index');
        Route::get('/datawedge-config', 'ProductionWeb\PickListController@getDatawedgeConfig');
        Route::get('/item', 'ProductionWeb\PickListController@getItem');
        Route::get('/items-by-ordernumber/{order_number}', 'ProductionWeb\PickListController@getItemsByOrderNumber');
        Route::get('/items-by-racknumber/{rack_number}', 'ProductionWeb\PickListController@getItemsByRackNumber');
        Route::get('/{id}', 'ProductionWeb\PickListController@get');
        Route::post('/', 'ProductionWeb\PickListController@update');
        Route::post('/u2', 'ProductionWeb\PickListController@update2');
    });

    Route::group(['prefix' => 'vinyl-pick-list'], function () {
        Route::get('/', 'ProductionWeb\VinylPickListController@index');
        Route::get('/item', 'ProductionWeb\VinylPickListController@getItem');
        Route::get('/item-from-screen-barcode/{barcode}', 'ProductionWeb\VinylPickListController@getItemFromScreenBarcode');
        Route::get('/item-from-su-barcode/{barcode}', 'ProductionWeb\VinylPickListController@getItemFromSUBarcode');
        Route::get('/items-by-ordernumber/{order_number}', 'ProductionWeb\VinylPickListController@getItemsByOrderNumber');
        Route::get('/items-by-racknumber/{rack_number}', 'ProductionWeb\VinylPickListController@getItemsByRackNumber');
        Route::get('/{id}', 'ProductionWeb\VinylPickListController@get');
        Route::post('/', 'ProductionWeb\VinylPickListController@update');
        Route::post('/u2', 'ProductionWeb\VinylPickListController@updateMulti');
    });

    Route::group(['prefix' => 'shipping'], function () {
        Route::post('clear-rack-contents/{rack_number}', 'ProductionWeb\ShippingController@clearRackContents');
        Route::get('customer-email/{division_id}/{ordernumber}', 'ProductionWeb\ShippingController@getCustomerEmail');
        Route::get('workorders/{division_id}/{branch}', 'ProductionWeb\ShippingController@getWorkorders');
        Route::get('server-time', 'ProductionWeb\ShippingController@getServerTime');
        Route::post('proof-of-delivery/{division}/{ordernumber}', 'ProductionWeb\ShippingController@postProofOfDelivery');
        Route::get('fix-pod', 'ProductionWeb\ShippingController@fixPOD');
    });

    Route::resource('scan_logs', 'ScanLogsController');

    Route::group(['prefix' => 'schedule'], function () {
        Route::get('app-ver', function () {
            return '1.0.0';
        });

        Route::get('colours/{department}', 'ProductionWeb\ScheduleController@colours');
        Route::get('colours2/{department}', 'ProductionWeb\ScheduleController@colours2');
        Route::get('departments', 'ProductionWeb\ScheduleController@departments');
        Route::get('departments2', 'ProductionWeb\ScheduleController@departments2');
        Route::get('subdepartments', 'ProductionWeb\ScheduleController@subdepartments');
        Route::get('details/{department}', 'ProductionWeb\ScheduleController@details');
        Route::get('dates/{department}', 'ProductionWeb\ScheduleController@scheduleDates');
        Route::get('dates2/{department}', 'ProductionWeb\ScheduleController@scheduleDates2');
        Route::get('headers/{department}', 'ProductionWeb\ScheduleController@headers');
        Route::get('headers2/{department}', 'ProductionWeb\ScheduleController@headers2');

        Route::get('glass-totals/{order_numbers}', 'ProductionWeb\ScheduleController@glassTotals');
        Route::get('glass-details/{order_numbers}', 'ProductionWeb\ScheduleController@glassDetails');
        Route::get('line-items/{schedule_ids}', 'ProductionWeb\ScheduleController@lineItems');

        Route::group(['before' => 'jwt-auth'], function () {
            Route::put('complete', 'ProductionWeb\ScheduleController@updateComplete');
            Route::post('comments', 'ProductionWeb\ScheduleController@addComment');
        });
    });

    Route::get('bay-areas/{division}', 'ProductionWeb\ShippingBayController@listBays');
    Route::get('bay-area/{rack_number}', 'ProductionWeb\ShippingBayController@getBayNumber');
    Route::get('bay-area2/{rack_number}', 'ProductionWeb\ShippingBayController@getBay');
    Route::put('bay-area', 'ProductionWeb\ShippingBayController@update');
});

Route::group(['prefix' => 'production/v2'], function () {
    Route::group(['prefix' => 'schedule'], function () {
        Route::get('app-ver', function () {
            return '0.6.0';
        });

        Route::get('departments', 'ProductionWeb\ScheduleTestingController@departments');
        Route::get('subdepartments', 'ProductionWeb\ScheduleTestingController@subdepartments');
        Route::get('colours/{department}', 'ProductionWeb\ScheduleTestingController@colours');
        Route::get('details/{department}', 'ProductionWeb\ScheduleTestingController@details');
        Route::get('dates/{department}', 'ProductionWeb\ScheduleTestingController@scheduleDates');
        Route::get('headers/{department}', 'ProductionWeb\ScheduleTestingController@headers');
    });
});

// Projects API
// , 'before' => 'jwt-auth'
Route::group(['before' => 'jwt-auth', 'prefix' => 'projects/v1'], function () {
    Route::get('/', function () {
        // Show API
        return View::make('showapi', array(
            'user_name' => '',
            'api_name' => 'Projects',
            'api_routes' => [
                [
                    'name' 			=> '/projects',
                    'description'	=> 'Project List',
                    'url' 			=> '/projects/v1/projects'
                ],
            ]
        ));
    });

    Route::resource('projects', 'ProjectsWeb\ProjectsController');

    Route::get('change-orders/count', 'ProjectsWeb\ChangeOrdersController@getCount');
    Route::resource('change-orders', 'ProjectsWeb\ChangeOrdersController');

    Route::get('commercial-contracts/count', 'ProjectsWeb\CommercialContractsController@getCount');
    Route::resource('commercial-contracts', 'ProjectsWeb\CommercialContractsController');

    Route::resource('contract-managers', 'ProjectsWeb\ContractManagersController');
    Route::resource('customers', 'ProjectsWeb\CustomersController');
    Route::resource('estimators', 'ProjectsWeb\EstimatorsController');
    Route::resource('sales_people', 'ProjectsWeb\SalesPeopleController');
    Route::resource('site-coordinators', 'ProjectsWeb\SiteCoordinatorsController');
    Route::resource('subcontractors', 'ProjectsWeb\SubcontractorsController');

    Route::get('calendar', 'ProjectsWeb\CalendarController@index');
    Route::get('history', 'ProjectsWeb\HistoryController@index');

    Route::get('charts/projects-tendered', 'ProjectsWeb\ChartController@getProjectsTendered');

    Route::get('lookups', 'ProjectsWeb\LookupsController@index');
    Route::get('lookups/projects', 'ProjectsWeb\LookupsController@projects');
    Route::get('lookups/change-orders', 'ProjectsWeb\LookupsController@changeOrders');
    Route::get('lookups/customer-contacts', 'ProjectsWeb\LookupsController@customerContacts');
    Route::get('lookups/project-date-types', 'ProjectsWeb\LookupsController@projectDateTypes');

    Route::get('reports/projects', 'ProjectsWeb\ReportsController@getProjects');
    Route::get('reports/change-orders', 'ProjectsWeb\ReportsController@getChangeOrders');
    Route::get('reports/red-book', 'ProjectsWeb\ReportsController@getRedBook');

    Route::get('search/{query}', 'ProjectsWeb\SearchController@search');

    Route::get('user-management/role', 'ProjectsWeb\AuthorizationsController@getRole');
    Route::resource('user-management', 'ProjectsWeb\AuthorizationsController');

    Route::put('forward-load/{project_id}', 'WMProjectsController@updateExpectedDeliveryDate');
});

Route::group(['prefix' => 'transmittals/v1'], function () {
    Route::get('/data/{project_number}/{template_name}', 'ProjectsWeb\TransmittalController@show');
    Route::post('/generate/distribution-checklist', 'ProjectsWeb\TransmittalController@generateDistributionChecklist');
    Route::post('/generate/{template_name}', 'ProjectsWeb\TransmittalController@generateTransmittal');
});

// Fab sketch API
Route::group(['prefix' => 'fab-sketches/v1'], function () {
    Route::get('frame', 'FabSketchController@getFrameFabSketch');
    Route::get('employee-name/{employee_id}', 'TimeClock\Controller\TimeClockController@getEmployeeName');
});

// Sign-off API
Route::group(['prefix' => 'sign-offs/v1'], function () {
    Route::get('/', function () {
        return Response::json('sign-offs api v1');
    });

    Route::get('inspections', 'SignOffs\Controller\SignoffController@getInspections');
    Route::get('sign-offs/{window_id}/{window_index}', 'SignOffs\Controller\SignoffController@getSignoffs');

    Route::get('projects', 'SignOffs\Controller\ProjectController@show');
    Route::get('projects/recent/{technician_id}', 'SignOffs\Controller\ProjectController@recent');
    Route::get('projects/search/{term}', 'SignOffs\Controller\ProjectController@search');
    Route::get('projects/{group_name}', 'SignOffs\Controller\ProjectController@showGroup');

    Route::get('project/{project_id}', 'SignOffs\Controller\ProjectController@find');
    Route::post('project/{project_id}/toggle-hold', 'SignOffs\Controller\ProjectController@toggleHold');
    Route::get('project/{project_id}/buildings', 'SignOffs\Controller\BuildingController@get');
    Route::get('project/{project_id}/building/{building_id}', 'SignOffs\Controller\BuildingController@find');
    Route::get('project/{project_id}/building/{building_id}/photo', 'SignOffs\Controller\BuildingController@getPhoto');
    Route::post('project/{project_id}/building/{building_id}/upload-photo', 'SignOffs\Controller\BuildingController@uploadPhoto');

    Route::get('project/{project_id}/building/{building_id}/floors', 'SignOffs\Controller\FloorController@get');
    Route::get('project/{project_id}/building/{building_id}/floor/{floor_id}', 'SignOffs\Controller\FloorController@find');
    Route::get('project/{project_id}/building/{building_id}/floor/{floor_id}/windows', 'SignOffs\Controller\WindowController@get');

    Route::get('reports/{project_id}/{report_id}', 'SignOffs\Controller\ReportController@get');

    Route::post('auth/token', 'SignOffs\Controller\LoginController@token');
    Route::post('auth/refresh', 'SignOffs\Controller\LoginController@refresh');

    Route::post('openings/hold', 'SignOffs\Controller\OpeningController@holdOpenings');
    Route::post('openings/release', 'SignOffs\Controller\OpeningController@releaseOpenings');
    Route::post('openings/comments', 'SignOffs\Controller\OpeningController@saveComments');

    Route::post('sign-offs/save', 'SignOffs\Controller\SignoffController@save');
});

// Quality Assurance API
Route::group(['prefix' => 'quality/v1'], function () {
    /**
      * Show API usage
      */
    Route::get('/', function () {
        $baseUri = '/quality/v1/ncmr';

        return View::make('showapi', array(
            'user_name' => '',
            'api_name' => 'Non-conforming Material Reports',
            'api_routes' => [
                [
                    'name' => $baseUri . '/external',
                    'description' => 'External NCMRs for quality-web app',
                    'url' => '/apps/api' . $baseUri . '/external'
                ],
                [
                    'name' => $baseUri . '/internal',
                    'description' => 'Internal NCMRs for quality-web app',
                    'url' => '/apps/api' . $baseUri . '/internal'
                ],
                [
                    'name' => $baseUri . '/lookups',
                    'description' => 'Lookup data for quality-web app',
                    'url' => '/apps/api' . $baseUri . '/lookups'
                ],
                [
                    'name' => $baseUri . '/materials',
                    'description' => 'materials data (from mfg.profiles) for quality-web app',
                    'url' => '/apps/api' . $baseUri . '/materials'
                ],
                [
                    'name' => $baseUri . '/materials-required',
                    'description' => 'materials required data (from workorders.materialsrequired) for quality-web app',
                    'url' => '/apps/api' . $baseUri . '/materials-required'
                ]
            ]
        ));
    });

    Route::group(['prefix' => 'labour'], function () {
        Route::get('cost', 'LabourStats\LabourStatsController@getCost');
        Route::get('distribution', 'LabourStats\LabourStatsController@getDistribution');
        Route::get('glazing-totals', 'LabourStats\LabourStatsController@getGlazingTotals');
        Route::get('minutes-per-frame-glazed', 'LabourStats\LabourStatsController@getMinutesPerFrameGlazed');
        Route::get('costing-vs-labour', 'LabourStats\LabourStatsController@getCostingVsLabour');
    });

    Route::group(['prefix' => 'portal'], function () {
        Route::get('report-types', 'PortalController@getReportTypes');
        Route::get('reports/{type_id}', 'PortalController@getReports');
    });

    Route::group(['prefix' => 'ncmr'], function () {
        Route::group(['prefix' => 'external'], function () {
            Route::get('download/{id}', 'NcmrExternalController@download');
            Route::get('next-report-number', 'NcmrExternalController@getNextReportNumber');
            Route::get('upgrade', 'NcmrExternalController@upgrade');
        });
        Route::resource('external', 'NcmrExternalController');

        Route::group(['prefix' => 'fabrication'], function () {
            Route::get('download/{id}', 'NcmrFabricationController@download');
            Route::get('get-line-info/{barcode}', 'NcmrFabricationController@getLineInfo');
            Route::get('current-report-number', 'NcmrFabricationController@getCurrentReportNumber');
            Route::get('get-sticker-data/{ncmr}', 'NcmrFabricationController@getStickerData');
            Route::get('next-report-number', 'NcmrFabricationController@getNextReportNumber');
            Route::get('get-failure-reasons', 'NcmrFabricationController@getFailureReasons');
            Route::get('upgrade', 'NcmrFabricationController@upgrade');
            //Route::get('test1', 'NcmrFabricationController@test1');
        });
        Route::resource('fabrication', 'NcmrFabricationController');

        Route::group(['prefix' => 'internal'], function () {
            Route::get('download/{id}', 'NcmrInternalController@download');
            Route::get('next-report-number', 'NcmrInternalController@getNextReportNumber');
            Route::get('upgrade', 'NcmrInternalController@upgrade');
        });

        Route::resource('internal', 'NcmrInternalController');
        Route::get('lookups/panels', 'NcmrController@getPanels');
        Route::get('lookups', 'NcmrController@getLookups');
        Route::get('materials', 'NcmrController@getMaterials');
        Route::get('materials-required', 'NcmrController@getMaterialsRequired');
        Route::get('process-all-attachments', 'NcmrController@processAllAttachments');
    });

    Route::group(['prefix' => 'production'], function () {
        Route::get('status-codes', 'ProductionQualityController@getStatusCodes');
    });

    Route::group(['before' => 'jwt-auth', 'prefix' => 'pqp'], function () {
        Route::resource('reports', 'QualityWeb\PQPReportController', array('only' => ['index', 'show', 'store', 'update']));

        Route::get('employee-locations', 'QualityWeb\PQPLookupController@getEmployeeLocations');
        Route::get('extrusion-quality-categories', 'QualityWeb\PQPLookupController@getExtrusionQualityCategories');
        Route::get('fabrication-types', 'QualityWeb\PQPLookupController@getFabricationTypes');
        Route::get('frame-series', 'QualityWeb\PQPLookupController@getFrameSeries');
        Route::get('inventory-categories', 'QualityWeb\PQPLookupController@getInventoryCategories');
        Route::get('inventory-types', 'QualityWeb\PQPLookupController@getInventoryTypes');
        Route::get('material-handling-categories', 'QualityWeb\PQPLookupController@getMaterialHandlingCategories');
        Route::get('non-conformance-departments', 'QualityWeb\PQPLookupController@getNonConformanceDepartments');
        Route::get('productivity-departments', 'QualityWeb\PQPLookupController@getProductivityDepartments');
        Route::get('sealed-unit-categories', 'QualityWeb\PQPLookupController@getSealedUnitCategories');

        Route::group(['prefix' => 'lookup'], function () {
            Route::get('booth-tests', 'QualityWeb\PQPLookupController@getBoothTests');
            Route::get('field-water-frame-tests', 'QualityWeb\PQPLookupController@getFieldWaterFrameTests');
            Route::get('field-water-opening-tests', 'QualityWeb\PQPLookupController@getFieldWaterOpeningTests');
            Route::get('forward-load', 'QualityWeb\PQPLookupController@getForwardLoad');
            Route::get('production-quality', 'QualityWeb\PQPLookupController@getProductionQuality');
        });
    });
});

// Work Order API
Route::group(['prefix' => 'workOrders/v1'], function () {
    Route::get('/', function () {
        // Show API
        return View::make('showapi', array(
            'auth_info' => get_user_info(),
            'api_name' => 'Work Orders',
            'api_routes' => [
                //
            ]
        ));
    });

    Route::get('active-projects', 'WMProjectsController@getActiveProjectList');
    Route::get('projectInfo', 'WMProjectsController@getProjectInfo');
    Route::get('buildingInfo', 'WMProjectsController@getBuildingInfo');
    Route::get('floorInfo', 'WMProjectsController@getFloorInfo');
    Route::get('orders-not-processed', 'WMProjectsController@getOrdersNotProcessed');
    Route::get('workOrders', 'WMProjectsController@getWorkOrders');

    Route::group(['prefix' => 'workorder-documents'], function () {
        Route::get('/', 'WorkOrderDocumentsController@getList');
        Route::post('coordinator-fabs', 'WorkOrderDocumentsController@postCoordinatorFab');
        Route::get('glass-files', 'WorkOrderDocumentsController@getGlassFileList');
        Route::post('glass-files', 'WorkOrderDocumentsController@postGlassImportFile');
    });

    Route::resource('workOrders2', 'ProcessingWeb\ProcessingLogController');
    Route::get('calendar', 'ProcessingWeb\ProcessingLogController@getCalendar');
    Route::get('productionSummary/{order_number}', 'WMProjectsController@getProductionSummary');
    Route::get('processingTotals', 'WMProjectsController@getProcessingTotals');

    Route::group(['prefix' => 'material-remnants'], function () {
        Route::get('/', 'Workorders\MaterialRemnantsController@listConfirmed'); // will replace with listAll
        Route::get('/confirmed', 'Workorders\MaterialRemnantsController@listConfirmed');
        Route::get('/confirmed-with-usedby', 'Workorders\MaterialRemnantsController@listConfirmedWithUsedBy');
        Route::get('/unconfirmed', 'Workorders\MaterialRemnantsController@listUnconfirmed');
        Route::get('/scrap-candidates', 'Workorders\MaterialRemnantsController@listScrapCandidates');
        Route::get('/search', 'Workorders\MaterialRemnantsController@search');
        Route::get('/last-used-cart', 'Workorders\MaterialRemnantsController@getLastUsedCart');
        Route::get('/cart/{cart_id}', 'Workorders\MaterialRemnantsController@getCart');
        Route::get('/{id}', 'Workorders\MaterialRemnantsController@get');
        Route::put('/{id}', 'Workorders\MaterialRemnantsController@confirm');
        Route::put('/resize/{id}', 'Workorders\MaterialRemnantsController@resize');
        Route::put('/transfer/{id}', 'Workorders\MaterialRemnantsController@transfer');
        Route::delete('/{id}', 'Workorders\MaterialRemnantsController@delete');
    });
});
