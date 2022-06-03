<?php
namespace Sitestein\FontAwesome\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Facades\Sitestein\FontAwesome\FontAwesome;

class FontAwesomeController extends CpController
{

    public function search(Request $request, $search)
    {
        return FontAwesome::search($search);
    }
}
