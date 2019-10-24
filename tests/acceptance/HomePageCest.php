<?php 

class HomePageCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function availableTest(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Doc Editor');
    }
}
