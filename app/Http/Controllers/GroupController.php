<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GroupController extends Controller
{
    function setGroupID(Request $request, $groupID) {
        
        // Store the groupID in the session
        session(['groupID' => $groupID]);
                        
        // Redirect to the desired page or display a success message
        return redirect('/admin')->with('success', 'Group ID set successfully!');
}
}
