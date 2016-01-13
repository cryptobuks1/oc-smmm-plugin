<?php namespace Piratmac\Smmm\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Flash;
use Lang;
use Cms\Classes\Page;
use RainLab\User\Components\Account;
use Piratmac\Smmm\Models\Portfolio as PortfolioModel;
use October\Rain\Exception\ApplicationException;
use October\Rain\Exception\SystemException;
use October\Rain\Exception\ValidationException;
use Illuminate\Support\Facades\Redirect;

class Portfolio extends ComponentBase
{

  /*
   * The portfolio being viewed / edited
   */
  public $portfolio;
  /*
   * Action: create, view or manage
   */
  public $action = 'view';

  /*
   * Page listing the portfolios
   */
  public $portfolioListPage = [];

  /*
   * Page for stock details
   */
  public $stockDetailsPage = [];


  public function componentDetails()
  {
    return [
      'name'    => 'piratmac.smmm::lang.components.portfolios_name',
      'description' => 'piratmac.smmm::lang.components.portfolios_description'
    ];
  }

  public function defineProperties()
  {
    return [
      'portfolio_id' => [
        'title'       => 'piratmac.smmm::lang.settings.portfolio_id',
        'description' => 'piratmac.smmm::lang.settings.portfolio_id_description',
        'default'     => '{{ :portfolio_id }}',
        'type'        => 'string'
      ],
      'action' => [
        'title'       => 'piratmac.smmm::lang.settings.action',
        'description' => 'piratmac.smmm::lang.settings.action_description',
        'default'     => '{{ :action }}',
        'type'        => 'string'
      ],
      'portfolioList' => [
        'title'       => 'piratmac.smmm::lang.settings.portfoliolist_page',
        'description' => 'piratmac.smmm::lang.settings.portfoliolist_description',
        'default'     => '',
        'type'        => 'dropdown'
      ],
      'stockDetails' => [
        'title'       => 'piratmac.smmm::lang.settings.stock_page',
        'description' => 'piratmac.smmm::lang.settings.stock_description',
        'default'     => '',
        'type'        => 'dropdown'
      ],
    ];
  }
  public function getportfolioListOptions()
  {
    return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
  }

  public function getstockDetailsOptions()
  {
    return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
  }




/**********************************************************************
                     Helper functions
**********************************************************************/

  protected function loadPortfolio($portfolio_id)
  {
    if ($portfolio_id == '' || !is_numeric($portfolio_id))
      throw new ApplicationException (trans('piratmac.smmm::lang.messages.error_no_id'));


    $portfolio = PortfolioModel::where('id', $portfolio_id)->firstOrFail();

    if (!$portfolio->checkUser())
      throw new ApplicationException (trans('piratmac.smmm::lang.messages.error_wrong_user'));

    return $portfolio;
  }

  private function getPortfolioDetails ($portfolio)
  {
    $portfolio->getHeldStocks();
    $portfolio->setHeldStocksLinks($this->controller, $this->stockDetailsPage);
    $portfolio->calculateValuation();
    $portfolio->getMovements();
    $portfolio->setMovementsLinks($this->controller, $this->stockDetailsPage);
    return $portfolio;
  }

/**********************************************************************
                     User actions
**********************************************************************/
  public function onRun()
  {
    $this->addJs('/plugins/piratmac/smmm/assets/js/bootstrap-datepicker.js');
    $this->addCss('/plugins/piratmac/smmm/assets/css/datepicker.css');
    $this->addJs('/plugins/piratmac/smmm/assets/js/smmm.js');
    $this->action = $this->param('action');
    $this->portfolioListPage = $this->property('portfolioList');
    $this->stockDetailsPage = $this->property('stockDetails');

    if ($this->action != 'create') {
      $this->portfolio = $this->loadPortfolio($this->property('portfolio_id'));
      $this->portfolio = $this->getPortfolioDetails($this->portfolio);
    }
  }

  public function init () {
    $this->portfolioListPage = $this->property('portfolioList');
  }

  public function onModifyPortfolio() {
    $this->portfolio = $this->loadPortfolio(post('id'));

    $result = $this->portfolio->onModify(post());
    $url = $this->pageUrl($this->page->baseFileName, ['portfolio_id' => $this->portfolio->id, 'action' => 'view']);
    Flash::success(trans('piratmac.smmm::lang.messages.success_modification'));
    return Redirect::to($url);
  }

  public function onCreatePortfolio() {
    $this->portfolio = new PortfolioModel(post());

    $result = $this->portfolio->onCreate();
    $url = $this->pageUrl($this->page->baseFileName, ['portfolio_id' => $this->portfolio->id, 'action' => 'view']);
    Flash::success(trans('piratmac.smmm::lang.messages.success_creation'));
    return Redirect::to($url);
  }


  public function onDeletePortfolio() {
    $this->portfolio = $this->loadPortfolio(post('id'));

    $result = $this->portfolio->onDelete();
    $url = $this->pageUrl($this->portfolioListPage);
    Flash::success(trans('piratmac.smmm::lang.messages.success_deletion'));
    return Redirect::to($url);
  }


}