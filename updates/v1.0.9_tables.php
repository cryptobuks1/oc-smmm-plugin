<?php namespace Piratmac\Smmm\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AssetValueAddIndex extends Migration
{

  public function up()
  {

    Schema::table('piratmac_smmm_asset_values', function($table)
    {
      $table->primary(['asset_id', 'date']);
    });


  }

  public function down () {}
}
