
<?php
/*  
 * DVD Store Browse Reviews PHP Postgresql Page - dsbrowsereviews.php
 *
 * Copyright (C) 2005 Dell, Inc. <davejaffe7@gmail.com> and <tmuirhead@vmware.com>
 *
 * Browse Reviews of products in Postgresql DVD store by author and title based on keywords
 *
 * Last Updated 12/15/21
 *
 * Support for PHP 7.4 and pgsql
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/ 

include("dscommon.inc");

ds_html_header("DVD Store Browse Product Reviews Page");

$customerid = $_REQUEST["customerid"];
$storenum = $_REQUEST["storenum"];
$review_title = isset($_REQUEST["review_title"]) ? $_REQUEST["review_title"] : NULL;
$review_actor = isset($_REQUEST["review_actor"]) ? $_REQUEST["review_actor"] : NULL;
$limit_num = isset($_REQUEST["limit_num"]) ? $_REQUEST["limit_num"] : NULL;
$search_depth = isset($_REQUEST["search_depth"]) ? $_REQUEST["search_depth"] : 500;
$browsereviewtype = isset($_REQUEST["browsereviewtype"]) ? $_REQUEST["browsereviewtype"] : NULL;
$productid = isset($_REQUEST["productid"]) ? $_REQUEST["productid"] : NULL;
// $selected_item = $_REQUEST["selected_item"];
// $item = $_REQUEST["item"];

if (empty($customerid))
  {
  echo "<H2>You have not logged in - Please click below to Login to DVD Store</H2>\n";
  echo "<FORM ACTION='./dslogin.php' METHOD=GET>\n";
  echo "<INPUT TYPE=SUBMIT VALUE='Login'>\n";
  echo "</FORM>\n";
  ds_html_footer();
  exit;
  }

echo "<H2>Browse for Product Reviews by Keyword in Title or Actor   </H2>\n";

echo "<FORM ACTION='./dsbrowsereviews.php' METHOD='GET'>\n";
echo "<INPUT NAME='browsereviewtype' TYPE=RADIO VALUE='title'"; if($browsereviewtype == 'title') echo "CHECKED";
echo ">Title  <INPUT NAME='review_title' VALUE='$review_title' TYPE=TEXT SIZE=15> <BR>\n";
echo "<INPUT NAME='browsereviewtype' TYPE=RADIO VALUE='actor'"; if($browsereviewtype == 'actor') echo "CHECKED";
echo ">Actor  <INPUT NAME='review_actor' VALUE='$review_actor' TYPE=TEXT SIZE=15> <BR>\n";
echo "Number of search results to return\n";
echo "<SELECT NAME='limit_num'>\n";
for ($i=1; $i<11; $i++)
  {
  if ($i == $limit_num)
    {echo "  <OPTION VALUE=$i SELECTED>$i</OPTION>\n";}
  else
    {echo "  <OPTION VALUE=$i>$i</OPTION>\n";}
  }
echo "</SELECT><BR>\n";

echo "<INPUT TYPE=HIDDEN NAME=customerid VALUE='$customerid'>\n";
echo "<INPUT TYPE=HIDDEN NAME=storenum VALUE=$storenum>\n";
echo "<INPUT TYPE=SUBMIT VALUE='Search'>\n";
echo "</FORM>\n";

if (!empty($browsereviewtype))
  {
  if (!($link_id = pg_connect($connstr))) die(pg_last_error());

  switch ($browsereviewtype)
    {
    case "title":
      $browsereview_query ="select * from get_prod_reviews_by_title$storenum ($limit_num,$search_depth,'$review_title');";
      break;
    case "actor":
      $browsereview_query ="select * from get_prod_reviews_by_actor$storenum ($limit_num,$search_depth,'$review_actor');";
      break;
    }

  $browsereviews_result = pg_query($link_id,$browsereview_query);

  if (pg_num_rows($browsereviews_result) == 0)
    {
    echo "<H2>No Reviews Found</H2>\n";
    }
  else
    {
    echo "<BR>\n";
    echo "<H2> Most Helpful Reviews matching keyword </H2>\n";
    while ($browsereviews_result_row = pg_fetch_row($browsereviews_result))
      {
      echo "----------------------------------------------------------------------------------------------<BR>";
      echo " $browsereviews_result_row[1] starring $browsereviews_result_row[2]<BR>\n";
      echo " Review Summary - $browsereviews_result_row[7]<BR>\n";
      echo " Rated $browsereviews_result_row[5] stars<BR>\n";
      echo " Review Created By $browsereviews_result_row[6] on $browsereviews_result_row[4]<BR>\n";
      echo " $browsereviews_result_row[8]<BR>\n";
      echo "<FORM ACTION='./dsnewhelpfulness.php' METHOD='GET'>\n";
      echo "Helpfulness ranking of this review (10 is most helpful) \n";
      $helpfulness_levels = array("1","2","3","4","5","6","7","8","9","10");

      echo "<SELECT NAME='review_helpfulness'>\n";
      for ($i=0; $i<count($helpfulness_levels); $i++)
        {
        $j=$i+1;
        echo "  <OPTION VALUE=$j>$helpfulness_levels[$i]</OPTION>\n";
        }
      echo "</SELECT><BR>\n";
      echo "<INPUT TYPE=HIDDEN NAME=customerid VALUE=$customerid>\n";
      echo "<INPUT TYPE=HIDDEN NAME=storenum VALUE=$storenum>\n";
      echo "<INPUT TYPE=HIDDEN NAME=reviewid VALUE=$browsereviews_result_row[3]>\n";
      echo "<INPUT TYPE=HIDDEN NAME=productid VALUE=$browsereviews_result_row[0]>\n";
      echo "<INPUT TYPE=HIDDEN NAME=helpfulness_sum VALUE=$browsereviews_result_row[9]>\n";
      echo "<INPUT TYPE='submit' VALUE='Submit Helpfulness Rating'>\n";
      echo "</FORM>\n";
      echo "<FORM ACTION='./dsnewreview.php' METHOD='GET'>\n";
      echo "OR \n";
      echo "<INPUT TYPE=HIDDEN NAME=customerid VALUE=$customerid>\n";
       echo "<INPUT TYPE=HIDDEN NAME=storenum VALUE=$storenum>\n";
      echo "<INPUT TYPE=HIDDEN NAME=productid VALUE=$browsereviews_result_row[0]>\n";
      echo "<INPUT TYPE=HIDDEN NAME=review_title VALUE='$browsereviews_result_row[1]'>\n";
      echo "<INPUT TYPE='submit' VALUE='Create a New Review'>\n";
      echo "</FORM>\n";

      }      
    pg_free_result($browsereviews_result);

    echo "</FORM>\n";
    }
  pg_close($link_id);
  }

ds_html_footer();
?>
