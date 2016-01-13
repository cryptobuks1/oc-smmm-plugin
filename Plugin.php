<?php namespace Piratmac\Smmm;

use System\Classes\PluginBase;
use Backend\Models\User;
use Lang;

/**
 * Smmm Plugin Information File
 */
class Plugin extends PluginBase
{

  /**
   * Returns information about this plugin.
   *
   * @return array
   */
  public function pluginDetails()
  {
    return [
      'name'        => 'piratmac.smmm::lang.plugin.name',
      'description' => 'piratmac.smmm::lang.plugin.description',
      'author'      => 'Piratmac <piratmac@gmail.com>',
      'icon'        => 'icon-leaf'
    ];
  }

  /**
   * @var array Plugin dependencies
   */
  public $require = ['RainLab.User'];

  public function registerComponents()
  {
    return [
      'Piratmac\Smmm\Components\Portfolio'  => 'portfolio',
      'Piratmac\Smmm\Components\Portfolios' => 'portfolios',
      'Piratmac\Smmm\Components\Stock' => 'stock',
      'Piratmac\Smmm\Components\Stocks' => 'stocks',
    ];
  }


  public function boot(){
    User::extend(function($model){
      $model->hasMany['portfolios'] = ['Piratmac\Smmm\Models\Portfolio'];
    });


  }

  public function registerMarkupTags()
  {
    return [
      'filters' => [
          'number_format_locale' => [$this, 'number_format_locale'],
          'number_format_count' => [$this, 'number_format_count'],
          'number_format_price' => [$this, 'number_format_price'],
          'number_format_amount' => [$this, 'number_format_amount'],


          'trans'        => function ($string) { return Lang::get($string); },
          'trans_label'  => function ($string) { return Lang::get('piratmac.smmm::lang.labels.'.$string); },
          'hide_if_zero' => function ($number) { return $number == 0?'':$number; },
      ],
      'functions' => [
          'form_select_options' => [$this, 'form_select_options'],
      ],
    ];
  }

  public function number_format_locale ($number,$decimals=-1) {
    // Let's try to keep enough precision
    if ($decimals == -1) {
      $decimals = ( (int) $number != $number ) ? (strlen($number) - strpos($number, '.')) - 1 : 0;
    }
    $locale = localeconv();
    return number_format($number,$decimals,
               $locale['decimal_point'],
               $locale['thousands_sep']);
  }

  public function number_format_count ($number) {
    return $this->number_format_locale($number, 3);
  }

  public function number_format_price ($number) {
    return $this->number_format_locale($number, 5);
  }

  public function number_format_amount ($number) {
    return $this->number_format_locale($number, 2);
  }

  public function form_select_options ($dropdown) {
    return Lang::get('piratmac.smmm::lang.dropdowns.'.$dropdown);
  }




}