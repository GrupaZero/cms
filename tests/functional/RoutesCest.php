<?php namespace Cms;

class RoutesCest {

    public function canSeeProperUrlsOfApiRoutes(FunctionalTester $I)
    {
        $I->assertEquals(apiUrl('contents'), route('api.contents'));
        $I->assertEquals(apiUrl('contents/12/translations'), route('api.contents.translations', 12));
    }
}