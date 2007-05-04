<?php
/*
tp_options_page.php

Handles everything related to the options page

Copyright (C) 2007 Eric D. Hough (k2eric@gmail.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 *  Main function for our options page
 */
function tp_options_subpanel()
{
	function_exists('tp_validOptions') || require("tp_options_logic.php");

    $dbOptions = tp_initOptions();

    $pageTitle = TP_MSG_OPTPANELTITLE;

    /* are we updating options? */
    if (isset($_POST['tubepress_save'])) {
        tp_update_options($dbOptions);
        $dbOptions = tp_initOptions();
    }

    print <<<EOT
    <div class="wrap">
          <form method="post">
        <h2>$pageTitle</h2>Set default options for the plugin. Each option here can be overridden 
        on any page that has your TubePress trigger tag.
        <br /><br />
EOT;

    tp_printHTML_searchArray($dbOptions[TP_OPTS_SEARCH], TP_MSG_WHICHVIDS,
        $dbOptions[TP_OPTS_SRCHV]);
    tp_printHTML_genericOptionsArray($dbOptions[TP_OPTS_DISP],
        TP_MSG_VIDDISP, 5);
    tp_printHTML_playerLocationMenu($dbOptions);
    tp_printHTML_metaArray($dbOptions[TP_OPTS_META],TP_MSG_META,$metas);
    tp_printHTML_genericOptionsArray($dbOptions[TP_OPTS_ADV],TP_MSG_ADV,20);

    $saveValue = TP_MSG_SAVE;
    print <<<EOT
        <input type="submit" name="tubepress_save" value="$saveValue" />
          </form>
     </div>
EOT;

}

/**
 * 
 */
function tp_printHTML_genericOptionsArray($theArray, 
    $arrayName, $inputSize = 20, $radioName = '')
{
    tp_printHTML_optionHeader($arrayName);

    $openBracket = "";
    $closeBracket = "";
    foreach ($theArray as $option) {

        if ($option->name == TP_OPT_KEYWORD) {
            $openBracket = '[';
            $closeBracket = ']';
        } else {
            $openBracket = "";
            $closeBracket = "";
        }
        print <<<EOT
                    <tr valign="top">
                        <th style="font-weight: bold; font-size: 1em" 
                            scope="row">$option->title:</th>
                        <td>
                            $openBracket<input name="$option->name" 
                                type="text" id="$option->name" class="code"
                                value="$option->value" size="$inputSize" />
                                $closeBracket
                            <br />$option->description
                        </td>

                    </tr>
EOT;
    }
    tp_printHTML_optionFooter();
}

/**
 * 
 */
function tp_printHTML_metaArray($theArray, $arrayName)
{
    tp_printHTML_optionHeader($arrayName);
    echo "<tr><td width='10%'></td><td><table cellspacing='0' " .
    		"cellpadding='0' width='100%'>";

    $logan = 0;
    foreach ($theArray as $metaOption) {

        $colCount = $logan % 5;

        $selected = "";
        if ($metaOption->value == true) $selected = "CHECKED";

        if ($colCount == 0) echo "<tr>";
print <<<EOT
            <td>
                <input type="checkbox" name="meta[]" value="$metaOption->name"
                $selected />
            </td>
            <td><b>$metaOption->title</b></td>
EOT;
        if ($colCount == 4) echo "</tr>";
        $logan++;
    }
    echo "</tr></table>";
    tp_printHTML_optionFooter();
}

/**
 * 
 */
function tp_printHTML_optionHeader($arrayName)
{
    print <<<EOT
            <fieldset>
EOT;
    if ($arrayName != "")
        echo '<h3>' . $arrayName . '</h3>';
print <<<EOT
                <table class="editform optiontable">
EOT;
}

/**
 * 
 */
function tp_printHTML_optionFooter() {
print <<<EOT
    </table>
                 </fieldset>
EOT;
}

/**
 * 
 */
function tp_printHTML_playerLocationMenu($dbOptions) {
    $locationVars =     $dbOptions[TP_OPTS_PLAYERLOCATION];
    $theArray =         $dbOptions[TP_OPTS_PLAYERMENU];
    $theOption =         $theArray[TP_OPT_PLAYIN];
    tp_printHTML_optionHeader("");

print <<<EOT
            <tr>
                <th style="font-weight: bold; font-size: 1em">
                $theOption->title</th>
            <td><select name="$theOption->name">
EOT;
    foreach ($locationVars as $location) {
        $selected = "";
        if ($location->name == $theOption->value)
            $selected = "selected";
        $inputBox = "";
print <<<EOT
        <option value="$location->name" $selected>$location->title</option>
EOT;
    }
print <<<EOT
        </select>
    </td>
        </tr>
EOT;
    tp_printHTML_optionFooter();
}

/**
 * 
 */
function tp_printHTML_quickSrchVal($value, $searchVars, $inputSize)
{
    $whichValue = "";
    switch ($value) {
        case TP_SRCH_TAG:
            $whichValue = TP_SRCH_TAGVAL;
            $inputSize = 40;
            break;
        case TP_SRCH_REL:
            $whichValue = TP_SRCH_RELVAL;
            $inputSize = 40;
            break;
        case TP_SRCH_USER: $whichValue = TP_SRCH_USERVAL;break;
        case TP_SRCH_PLST: $whichValue = TP_SRCH_PLSTVAL;break;
        case TP_SRCH_POPULAR: $whichValue = TP_SRCH_POPVAL;break;
        //case TP_SRCH_CATEGORY: $whichValue = TP_SRCH_CATVAL;break;
        case TP_SRCH_FAV: $whichValue = TP_SRCH_FAVVAL;break;
    }
    return '<input type="text" name="' . $searchVars[$whichValue]->name 
        . '" size="' . $inputSize . '" value="' 
        . $searchVars[$whichValue]->value
        . '" />';
}

/**
 * 
 */
function tp_printHTML_searchArray($theArray, $arrayName, 
    $searchVars, $inputSize=20)
{
    tp_printHTML_optionHeader($arrayName);

    $radioName = TP_OPT_SEARCHBY;

    foreach ($theArray as $option) {
        $selected = "";
        if ($option->name == $searchVars[TP_OPT_SEARCHBY]->value)
            $selected = "CHECKED";
        $inputBox = "";
        
        /* The idea here is only one mode that doesn't need any kind of input */
        if ($option->name != TP_SRCH_FEATURED) {
                $inputBox = tp_printHTML_quickSrchVal($option->name, 
                    $searchVars, $inputSize);
        }
        if ($option->name == TP_SRCH_POPULAR) {
            $name = TP_SRCH_POPVAL;
            $inputBox = '<select name="' . $name . '">';
            $period = array("day", "week", "month");
            foreach ($period as $thisPeriod) {
                $inputBox .= '<option value="' . $thisPeriod . '"';
                if ($thisPeriod == $searchVars[TP_SRCH_POPVAL]->value) {
                    $inputBox .= ' SELECTED';
                }
                $inputBox .= '>' . $thisPeriod . '</option>';
            }
            $inputBox .= '</select>';
        }
print <<<EOT
        <tr>
            <th style="font-weight: bold; font-size: 1em" valign="top">$option->title</th>
            <td>
                <input type="radio" name="$radioName" id="$option->name" value="$option->name" $selected /> $inputBox
                <br />$option->description
            </td>
        </tr>
EOT;
    }
    tp_printHTML_optionFooter();
}
?>
