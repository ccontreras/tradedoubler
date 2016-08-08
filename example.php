<?php

require_once('tradedoubler/Tradedoubler.php');

use tradedoubler\Tradedoubler;

$tradedoubler = new Tradedoubler('44D41DD809F1C630E9A99E6A8F244E963012FDA2');
$categoryTrees = $tradedoubler->getServiceData('advertisers.products.categories', array('language' => 'es'));


function renderCategoryTrees(array $categories, $level = 0) {
  $html = "<ul id=\"level-$level\">";

  foreach ($categories as $category) {
    if (isset($category['subCategories'])) {
      $html .= renderCategoryTrees($category['subCategories'], ++$level);
    } else {
      $html .= "<li><p>{$category['name']} - <span>({$category['productCount']})</span></p></li>";
    }
  }

  $html .= '</ul>';

  return $html;
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Tradedoubler API</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

    <div class="container">
      <?php if ($categoryTrees): ?>
        <?php echo renderCategoryTrees($categoryTrees['categoryTrees']); ?>
      <?php endif; ?>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
  </body>
</html>
