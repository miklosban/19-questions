<?php
require 'local/config.php';
require 'sources/autoload.php';

$NQ = new \NineteenQ\Engine(empty($_GET['q']) ? '' : $_GET['q']);
$numQuestions = count($NQ->askedQuestions);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="robots" content="noindex" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>19 kérdés</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/common.css">
    <script>var q="<?= $NQ->state ?>"</script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/inputs-ext/typeaheadjs/lib/typeahead.js-bootstrap.css" rel="stylesheet" media="screen">
    <script src="//cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.2/typeahead.bundle.min.js"></script>
    <style>
      body {background: #F1EBEB; margin-top: 2em}
    </style>
  </head>
  <body>
    <div class="container">
      <h2>19 kérdés <small class="text-secondary">gondolj egy állatra, megpróbálom kitalálni...</small></h2>
      <div class="jumbotron">
<?php if (isset($_GET['wrapup']) || $numQuestions >= 40): ?>
        <p class="display-2 text-success">Te nyerted ezt a kört!</p>
        <h3>Ezekből gondoltál valamire?</h3>
<?php
foreach ($NQ->getTopHunches() as $hunch) {
  $nameHtml = htmlspecialchars($hunch->name);
  if (!empty($hunch->subname)) $nameHtml .= '<small> (<i>' . htmlspecialchars($hunch->subname) . '</i>)</small>';
  #if (!empty($hunch->link)) $nameHtml = "<a href='{$hunch->link}'>$nameHtml</a>";
  echo "<a class=\"btn btn-secondary\" href=\"save.php&#63;obj={$hunch->objectId}&amp;q=".$NQ->state."\">{$nameHtml}</a>\n";
}
?>

<hr>
<h3>Ha nem, kérlek mondd meg mire gondoltál:</h3>

<form class="form form-inline mx-auto" action="save.php" method="get">
<input name="q" type="hidden" value="<?= $NQ->state ?>">
<input name="objectname" id="theobjectname" class="form-control">
<input type=submit value="Submit" class="btn btn-default">
</form>

<script>
var bestPictures = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      remote: 'api/v1/objects.php?q=%QUERY'
});
bestPictures.initialize();
$('#theobjectname').typeahead(null, {
  name: 'best-pictures',
  displayKey: 'name',
  source: bestPictures.ttAdapter(),
  templates: {
    suggestion: function(datum){return datum['name']}
  }
});
</script>
</div></body></html>
<?php exit; ?>
<?php endif; ?>


<?php

  if ($numQuestions >= 19)
  {
  	echo "<div class=\"alert alert-info\"><strong>Te nyerted ezt a kört!</strong><br>Folytathatod néhány további kérdésre válaszolva, vagy <a href=\"".basename($_SERVER['PHP_SELF'])."&#63;wrapup=yes&amp;q=".$NQ->state."\" class=\"btn btn-info\">mondd meg mire gondoltál</a> hogy tanítsál!</div>\n";
  }

      list($text, $subtext, $choices, $link) = $NQ->getNextQuestion();
      echo "<p class=\"lead\"><span style='font-weight:bold'>#". ($numQuestions+1) ."</span> ";
      if (strlen($subtext)) $text .= " (<i>$subtext</i>)";
      if (strlen($link))
        echo "<a href='$link' target='_blank'>$text</a>? ";
      else {
          echo $text;
          if (count($choices))
              echo "?";
          echo " ";
        }
  

      foreach ($choices as $choice)
      {
        if (preg_match('/w([0-9]+)/',$choice[1],$regs))
          $prefix = "save.php&#63;obj={$regs[1]}&amp;q=";
        else
          $prefix = basename($_SERVER['PHP_SELF']).'&#63;'.(isset($_GET['debug'])?'debug&amp;':'')."q=";
        echo "<a class=\"btn btn-outline-primary\" rel=\"nofollow\" href=\"$prefix".$choice[1]."\">".$choice[0]."</a> ";
      }
      echo "</p>";
  echo "<hr />";

  foreach (array_reverse($NQ->askedQuestions) as $pastQuestion)
  {
    list($name, $subtext, $answer) = $pastQuestion;
    if (strlen($subtext)) $name .= " ($subtext)";
    echo "<p>".htmlentities($name)." &mdash; $answer</p>";
  }
?>
      </div>

<?php
  if (isset($_GET['debug'])) {
?>
      <div class="row">
        <div class="col-md">
          <h2>Előérzetek</h2>
          <table class="table table-condensed">
<?php
    foreach ($NQ->getTopHunches() as $hunch)
    {
      $htmlName = htmlspecialchars($hunch->name);
      if (!empty($hunch->subname)) {
        $htmlName .= ' <small><em>(' . htmlspecialchars($hunch->subname) . ')</em></small>';
      }
      echo '<tr><td>' . $htmlName . '<td>' . number_format($hunch->likelihood*100 / $NQ->objectSumLikelihood, 3) . '%';
      echo '<tr><td colspan=2>';
      echo '<div class="progress">';
      echo '<div class="progress-bar bg-info" title="' . number_format($hunch->likelihood*100 / $NQ->objectSumLikelihood, 3) . '% likelihood" role="progressbar" style="width: ' . number_format($hunch->likelihood*100 / $NQ->objectSumLikelihood, 3) . '%"></div>';
      echo '</div>';
    }
    echo '<tr><td><em>Total entropy:<td>' . number_format($NQ->objectEntropy, 2) . ' bits';
?>
          </table>
        </div>
        <div class="col-md">
          <h2>Top kérdések</h2>
          <table class="table table-condensed">
<?php
    foreach ($NQ->getBestQuestions(10) as $question) {
      list($score, $id, $name, $sub, $yes, $no) = $question;
      if (strlen($sub)) $name .= " <small><em>($sub)</em></small>";
      echo "<tr><td>$name<td>" . number_format($score, 3) . " bits";
      echo "<tr><td colspan=2>";
      echo '<div class="progress">';
      echo '<div class="progress-bar bg-success" title="' . number_format($yes*100, 2) . '% yes" role="progressbar" style="width: ' . number_format($yes*100, 2) . '%"></div>';
      echo '<div class="progress-bar bg-info" title="' . number_format((1-$yes-$no)*100, 2) . '% skip" role="progressbar" style="width: ' . number_format((1-$yes-$no)*100, 2) . '%"></div>';
      echo '<div class="progress-bar bg-danger" title="' . number_format($no*100, 2) . '% no" role="progressbar" style="width: ' . number_format($no*100, 2) . '%"></div>';
      echo '</div>';
    }
?>
          </table>
        </div>
<!--
        <div class="col-md">
          <h2>Profiling</h2>
          <table class="table table-condensed">
<?php
    foreach ($NQ->debug as $title=>$line)
    {
      echo '<tr><td><strong>' . htmlspecialchars($title) . '</strong> ';
      echo htmlspecialchars(json_encode($line));
    }
?>
          </table>
        </div>
-->
<?php
  } else {
?>
        <div><a class="btn btn-info" href="?debug&amp;q=<?= $NQ->state ?>">Show Brain Dump</a></div>
<?php
  }
?>
    </div>
  </body>
</html>
