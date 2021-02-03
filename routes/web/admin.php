<?php

Route::group(['prefix' => '/admin', 'middleware' => ['auth', 'role:admin']], function () {
    Route::get('/', 'AdminController@dashboard');
    Route::get('/general-settings', 'AdminController@generalSettings');
    Route::post('/general-settings', 'AdminController@updateGeneralSettings');
    Route::post('/home-settings', 'AdminController@updateHomeSettings');

    Route::get('/user-settings', 'AdminController@userSettings');
    Route::post('/user-settings', 'AdminController@updateUserSettings');

    // Route::get('/page-settings', 'AdminController@pageSettings');
    // Route::post('/page-settings', 'AdminController@updatePageSettings');

    // Route::get('/group-settings', 'AdminController@groupSettings');
    // Route::post('/group-settings', 'AdminController@updateGroupSettings');

    // Route::get('/custom-pages', 'AdminController@listCustomPages');
    // Route::get('/custom-pages/create', 'AdminController@createCustomPage');
    // Route::post('/custom-pages', 'AdminController@storeCustomPage');
    // Route::get('/custom-pages/{id}/edit', 'AdminController@editCustomPage');
    // Route::post('/custom-pages/{id}/update', 'AdminController@updateCustomPage');

    // Route::get('/announcements', 'AdminController@getAnnouncements');
    // Route::get('/announcements/create', 'AdminController@createAnnouncement');
    // Route::get('/announcements/{id}/edit', 'AdminController@editAnnouncement');
    // Route::post('/announcements/{id}/update', 'AdminController@updateAnnouncement');
    // Route::post('/announcements', 'AdminController@addAnnouncements');
    // Route::get('/activate/{announcement_id}', 'AdminController@activeAnnouncement');

    Route::get('/themes', 'AdminController@themes');
    Route::get('/change-theme/{name}', 'AdminController@changeTheme');

    Route::get('/users', 'AdminController@showUsers');
    Route::get('/users/{username}/edit', 'AdminController@editUser');
    Route::post('/users/{username}/edit', 'AdminController@updateUser');
    Route::get('/users/{user_id}/delete', 'AdminController@deleteUser');

    Route::get('/users/{username}/delete', 'UserController@deleteMe');
    Route::post('/users/{username}/newpassword', 'AdminController@updatePassword');

    // Route::get('/pages', 'AdminController@showPages');
    // Route::get('/pages/{username}/edit', 'AdminController@editPage');
    // Route::post('/pages/{username}/edit', 'AdminController@updatePage');
    // Route::get('/pages/{page_id}/delete', 'AdminController@deletePage');


    // Route::get('/groups', 'AdminController@showGroups');
    // Route::get('/groups/{username}/edit', 'AdminController@editGroup');
    // Route::post('/groups/{username}/edit', 'AdminController@updateGroup');
    // Route::get('/groups/{group_id}/delete', 'AdminController@deleteGroup');


    Route::get('/manage-reports', 'AdminController@manageReports');
    Route::post('/manage-reports', 'AdminController@updateManageReports');
    Route::get('/mark-safe/{report_id}', 'AdminController@markSafeReports');
    Route::get('/delete-post/{report_id}/{post_id}', 'AdminController@deletePostReports');

    // Route::get('/manage-ads', 'AdminController@manageAds');
    Route::get('/update-database', 'AdminController@getUpdateDatabase');
    Route::post('/update-database', 'AdminController@postUpdateDatabase');
    Route::get('/get-env', 'AdminController@getEnv');
    Route::post('/save-env', 'AdminController@saveEnv');
    // Route::post('/manage-ads', 'AdminController@updateManageAds');
    Route::get('/settings', 'AdminController@settings');
    Route::get('/markpage-safe/{report_id}', 'AdminController@markPageSafeReports');
    // Route::get('/deletepage/{page_id}/{status}', 'AdminController@deletePage');
    Route::get('/deleteuser/{username}', 'UserController@deleteMe');
    // Route::get('/deletegroup/{group_id}', 'AdminController@deleteGroup');

    // Route::get('/category/create', 'AdminController@addCategory');
    // Route::post('/category/create', 'AdminController@storeCategory');
    // Route::get('/category/{id}/edit', 'AdminController@editCategory');
    // Route::post('/category/{id}/update', 'AdminController@updateCategory');

    // Route::get('/events', 'AdminController@getEvents');
    // Route::get('/events/{username}/edit', 'AdminController@editEvent');
    // Route::post('/events/{username}/edit', 'AdminController@updateEvent');
    // Route::get('/events/{event_id}/delete', 'AdminController@removeEvent');

    // Route::get('/event-settings', 'AdminController@eventSettings');
    // Route::post('/event-settings', 'AdminController@updateEventSettings');

    Route::get('/wallpapers', 'AdminController@wallpapers');
    Route::post('/wallpapers', 'AdminController@addWallpapers');
    Route::get('/wallpaper/{wallpaper}/delete', 'AdminController@deleteWallpaper');
});