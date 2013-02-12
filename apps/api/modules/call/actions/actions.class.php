<?php

/**
 * call actions.
 *
 * @package    OpenPNE
 * @subpackage main
 * @author     Your name here
 */
class callActions extends sfActions
{

  /**
   * Executes index action
   *
   * @param sfWebRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward('default', 'module');
  }

  public function executeSearch(sfWebRequest $request)
  {
    $id = (int) $request->getParameter("id", null);
    return $this->renderText($this->sheeturl2json($id));
  }

  public function executeDemo(sfWebRequest $request)
  {
  	$tel = $request->getParameter("tel");
  	$body = $request->getParameter("body");
  	$body = str_replace(array("\r\n","\r","\n"), '', $body);
		TejimayaBoundioUtil::pushcall($tel,$body);
		//TejimayaBoundioUtil::pushcall("08040600334","あおｓんつはそにぇうｓなおへうあのえう");
		return $this->renderText(json_encode(array("sutatus" => "success","tel" => $tel,"text" => $text)));
	}

  private function sheeturl2json($id = "1")
  {
    // Set your CSV feed
    $feed = 'https://docs.google.com/spreadsheet/pub?key=0AkrtLQHh8XpBdDlFcFlvU3QzdUViTWlHZFFhZFkwTWc&single=true&gid='.$id.'&output=csv';

    // Arrays we'll use later
    $keys = array();
    $newArray = array();


    // Do it
    $data = $this->csvToArray($feed, ',');

    // Set number of elements (minus 1 because we shift off the first row)
    $count = count($data) - 1;

    //Use first row for names  
    $labels = array_shift($data);

    foreach ($labels as $label) {
      $keys[] = $label;
    }

    // Add Ids, just in case we want them later
    $keys[] = 'id';

    for ($i = 0; $i < $count; $i++) {
      $data[$i][] = $i;
    }

    // Bring it all together
    for ($j = 0; $j < $count; $j++) {
      $d = array_combine($keys, $data[$j]);
      $newArray[$j] = $d;
    }

    // Print it out as JSON
    return json_encode($newArray);
  }

  function csvToArray($file, $delimiter)
  {
    if (($handle = fopen($file, 'r')) !== FALSE)
    {
      $i = 0;
      while (($lineArray = fgetcsv($handle, 4000, $delimiter, '"')) !== FALSE) {
        for ($j = 0; $j < count($lineArray); $j++) {
          $arr[$i][$j] = $lineArray[$j];
        }
        $i++;
      }
      fclose($handle);
    }
    return $arr;
  }

}