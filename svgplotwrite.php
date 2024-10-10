<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  // $str = stripslashes($_POST['str']);
  $str = $_POST['str'];
  $fontsize = 40;

  function xi2o($xi) {
    global $xparam,$yparam,$sideaxes;
    if (!$sideaxes)
      $xo = (($xi-$xparam[0])/$xparam[2]+1)*100;
    else
      $xo = (($xi-$xparam[0])/$xparam[2])*100;
    return $xo;
  }

  function yi2o($yi) {
    global $xparam,$yparam,$sideaxes;
    if (!$sideaxes) {
      $ymax = (($yparam[1]-$yparam[0])/$yparam[2]+2)*100;
      $yo = $ymax-(($yi-$yparam[0])/$yparam[2]+1)*100;
    }
    else {
      $ymax = (($yparam[1]-$yparam[0])/$yparam[2]+1)*100;
      $yo = $ymax-(($yi-$yparam[0])/$yparam[2])*100;
    }
    return $yo;
  }

  function QBezier($x0,$y0,$x1,$y1,$x2,$y2) {
    if (($x0==$x1)||($x1==$x2)||($x2==$x0))
      return "L ".xi2o($x1)." ".yi2o($y1)." ";   
    $m0 =  $y0*(2*$x0-($x1+$x2))/($x0-$x1)/($x0-$x2)
          +$y1*(2*$x0-($x2+$x0))/($x1-$x2)/($x1-$x0)
          +$y2*(2*$x0-($x0+$x1))/($x2-$x0)/($x2-$x1);
    $m1 =  $y0*(2*$x1-($x1+$x2))/($x0-$x1)/($x0-$x2)
          +$y1*(2*$x1-($x2+$x0))/($x1-$x2)/($x1-$x0)
          +$y2*(2*$x1-($x0+$x1))/($x2-$x0)/($x2-$x1);
    if ($m0==$m1) return "L ".xi2o($x1)." ".yi2o($y1)." "; 
    $xb = ($m1*$x1-$m0*$x0-$y1+$y0)/($m1-$m0);
    $yb = $m0*$xb - $m0*$x0 + $y0;
    return "Q ".xi2o($xb)." ".yi2o($yb)." ".xi2o($x1)." ".yi2o($y1)." ";
  }

  function QBezier2($x0,$y0,$x1,$y1,$x2,$y2) {
    if (($x0==$x1)||($x1==$x2)||($x2==$x0))
      return "L ".xi2o($x2)." ".yi2o($y2)." ";
    $m1 =  $y0*(2*$x1-($x1+$x2))/($x0-$x1)/($x0-$x2)
          +$y1*(2*$x1-($x2+$x0))/($x1-$x2)/($x1-$x0)
          +$y2*(2*$x1-($x0+$x1))/($x2-$x0)/($x2-$x1);
    $m2 =  $y0*(2*$x2-($x1+$x2))/($x0-$x1)/($x0-$x2)
          +$y1*(2*$x2-($x2+$x0))/($x1-$x2)/($x1-$x0)
          +$y2*(2*$x2-($x0+$x1))/($x2-$x0)/($x2-$x1);
    if ($m1==$m2) return "L ".xi2o($x2)." ".yi2o($y2)." ";
    $xb = ($m2*$x2-$m1*$x1-$y2+$y1)/($m2-$m1);
    $yb = $m1*$xb - $m1*$x1 + $y1;
    return "Q ".xi2o($xb)." ".yi2o($yb)." ".xi2o($x2)." ".yi2o($y2)." ";
  }


  if ($str!='') {

    preg_match_all('/xlabel(?:\[(.+)\])?=(.+)/', $str, $matches, PREG_SET_ORDER);
      $xlabeloffset = (float)trim($matches[0][1]);
      $xlabel = trim($matches[0][2]);
//    preg_match_all('/xlabel=(.+)/', $str, $matches, PREG_SET_ORDER);
//    $xlabel = trim($matches[0][1]);
    preg_match_all('/ylabel=(.+)/', $str, $matches, PREG_SET_ORDER);
    $ylabel = trim($matches[0][1]);
    preg_match_all('/xparam=(.+)/', $str, $matches, PREG_SET_ORDER);
    $xparam = preg_split('/,/', $matches[0][1]);
    preg_match_all('/yparam=(.+)/', $str, $matches, PREG_SET_ORDER);
    $yparam = preg_split('/,/', $matches[0][1]);
    preg_match_all('/subdiv=(.+)/', $str, $matches, PREG_SET_ORDER);
    $subdiv = trim($matches[0][1]);
    preg_match_all('/gridsize=(.+)/', $str, $matches, PREG_SET_ORDER);
    $gridsize = (float)trim($matches[0][1]);
    $nolabels = FALSE; if (strstr($str,"\\nolabels")) $nolabels = TRUE;
    $nogrids = FALSE; if (strstr($str,"\\nogrids")) $nogrids = TRUE;
    $nolabelnums = FALSE; if (strstr($str,"\\nolabelnums")) $nolabelnums = TRUE;
    $noaxes = FALSE; if (strstr($str,"\\noaxes")) $noaxes = TRUE;
    // 'sideaxes' option will affect xi2o, yi2o, nx-nygrids, svg viewbox size
    $sideaxes = FALSE; if (strstr($str,"\\sideaxes")) $sideaxes = TRUE;

    $patterns = array('/\^\{(.+?)\}/',
                      '/\_\{(.+?)\}/',
                      '/\\\\it\{(.+?)\}/');
/*  $replaces = array('<tspan baseline-shift="super">$1</tspan>',
                      '<tspan baseline-shift="sub">$1</tspan>'); */
    $replaces = array('<tspan dy="-'.($fontsize*0.3).'" font-size="'.($fontsize*0.6).'">$1</tspan><tspan dy="+'.($fontsize*0.3).'" font-size="1">|</tspan>',
                      '<tspan dy="'.($fontsize*0.3).'" font-size="'.($fontsize*0.6).'">$1</tspan><tspan dy="-'.($fontsize*0.3).'" font-size="1">|</tspan>',
                      '<tspan font-style="italic">$1</tspan>');

    $xlabel = preg_replace($patterns, $replaces, $xlabel);
    $ylabel = preg_replace($patterns, $replaces, $ylabel);

    if (!$sideaxes) {
      $nxgrids = ($xparam[1]-$xparam[0])/$xparam[2]+3;
      $nygrids = ($yparam[1]-$yparam[0])/$yparam[2]+2;
    }
    else {
      $nxgrids = ($xparam[1]-$xparam[0])/$xparam[2]+2;
      $nygrids = ($yparam[1]-$yparam[0])/$yparam[2]+1;
    }


// ***** Check if there is any curve, line, dash, mark, points *****

    preg_match_all('/curve(?:\[(.+)\])?=(.+)/', $str, $matches, PREG_SET_ORDER);
    $ncurve=count($matches);
    for ($i=0;$i<$ncurve;$i++) {
      $curvecolor[$i] = trim($matches[$i][1]);
      if ($curvecolor[$i]=="") $curvecolor[$i] = "black";
      $curvedata[$i] = preg_split('/[\s,;]+/', trim($matches[$i][2]));
    }
    preg_match_all('/line(?:\[(.+)\])?=(.+)/', $str, $matches, PREG_SET_ORDER);
    $nline=count($matches);
    for ($i=0;$i<$nline;$i++) {
      $linecolor[$i] = trim($matches[$i][1]);
      if ($linecolor[$i]=="") $linecolor[$i] = "black";
      $linedata[$i] = preg_split('/[\s,;]+/', trim($matches[$i][2]));
    }
    preg_match_all('/dash(?:\[(.+)\])?=(.+)/', $str, $matches, PREG_SET_ORDER);
    $ndash=count($matches);
    for ($i=0;$i<$ndash;$i++) {
      $dashcolor[$i] = trim($matches[$i][1]);
      if ($dashcolor[$i]=="") $dashcolor[$i] = "black";
      $dashdata[$i] = preg_split('/[\s,;]+/', trim($matches[$i][2]));
    }
    preg_match_all('/points(?:\[(.+)\])?=(.+)/', $str, $matches, PREG_SET_ORDER);
    $npoints=count($matches);
    for ($i=0;$i<$npoints;$i++) {
      $pointscolor[$i] = trim($matches[$i][1]);
      if ($pointscolor[$i]=="") $pointscolor[$i] = "black";
      $pointsdata[$i] = preg_split('/[\s,;]+/', trim($matches[$i][2]));
    }
    preg_match_all('/mark(?:\[(.+)\])?=(.+)/', $str, $matches, PREG_SET_ORDER);
    $nmark=count($matches);
    for ($i=0;$i<$nmark;$i++) {
      $markparam[$i] = preg_split('/[\s,;]+/', trim($matches[$i][1]));
      if ($pointscolor[$i]=="") $pointscolor[$i] = "black";
      $markdata[$i] = preg_split('/[\s,;]+/', trim($matches[$i][2]));
      $markdata[$i][2] = preg_replace($patterns, $replaces, $markdata[$i][2]);
    }


// ***** Change the euqation (if any) to data points *****

    include('evalmath.class.php');
    preg_match_all('/equation(?:\[(.+)\])?=(.+)/', $str, $matches, PREG_SET_ORDER);
    $nequation=count($matches);
    for ($i=0;$i<$nequation;$i++) {
      $m = new EvalMath;
      $equationcolor[$i] = trim($matches[$i][1]);
      if ($equationcolor[$i]=="") $equationcolor[$i] = "black";
      $m->evaluate('f(x) = '.trim($matches[$i][2]));
      // Use 10 parts to plot the equation
      $parts=100; $k=0;
      for ($j=0;$j<=$parts;$j++) {
        $x = $xparam[0]+$j*($xparam[1]-$xparam[0])/$parts;
        $m->evaluate('x = '.$x);
        $y = $m->evaluate('f(x)');
        if (is_numeric($m->evaluate('f(x)'))) {
          $equationdata[$i][2*$k]   = $x;
          $equationdata[$i][2*$k+1] = $y;
          $k++;
        }
      }
    }


// ***** Basic Information *****

    $output  = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $output .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"';
    $output .= ' "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'."\n";
    if (!$sideaxes)
      $output .= '<svg width="'.(($nxgrids+0.2)*$gridsize).'cm" height="'.(($nygrids+0.2)*$gridsize).'cm" viewBox="-10 -10 '.($nxgrids*100+20).' '.($nygrids*100+$xlabeloffset+20).'"  xmlns="http://www.w3.org/2000/svg" version="1.1" font-family="Sans,Arial">'."\n";
    else  
      $output .= '<svg width="'.(($nxgrids+0.2)*$gridsize).'cm" height="'.(($nygrids+0.2)*$gridsize).'cm" viewBox="-110 -10 '.($nxgrids*100+120).' '.($nygrids*100+$xlabeloffset+120).'"  xmlns="http://www.w3.org/2000/svg" version="1.1" font-family="Sans,Arial">'."\n";


    $output .= '<marker id="bigArrow" viewBox="0 0 20 20" refX="20" refY="10"
 markerUnits="strokeWidth" markerWidth="4" markerHeight="4"
 orient="auto"><path d="M 0 0 L 20 10 L 0 20 z" stroke="dimgrey"/></marker>'."\n";



    if (!$nogrids) {

      $output .= '<rect x="0" y="0" width="'.($nxgrids*100).'" height="'.($nygrids*100).'" fill="none" stroke="dimgrey" stroke-width="4" />'."\n";

      // ***** Sub Grid Lines *****

      for ($i=1;$i<$nxgrids*$subdiv;$i++)
        $output .= '<line x1="'.($i*100/$subdiv).'" y1="0" x2="'.($i*100/$subdiv).'" y2="'.($nygrids*100).'"  stroke="grey" stroke-width="2" />'."\n";
      for ($i=1;$i<$nygrids*$subdiv;$i++)
        $output .= '<line x1="0" y1="'.($i*100/$subdiv).'" x2="'.($nxgrids*100).'" y2="'.($i*100/$subdiv).'"  stroke="grey" stroke-width="2" />'."\n";

      // ***** Main Grid Lines *****

      for ($i=1;$i<$nxgrids;$i++)
        $output .= '<line x1="'.($i*100).'" y1="0" x2="'.($i*100).'" y2="'.($nygrids*100).'"  stroke="dimgrey" stroke-width="4" />'."\n";
      for ($i=1;$i<$nygrids;$i++)
        $output .= '<line x1="0" y1="'.($i*100).'" x2="'.($nxgrids*100).'" y2="'.($i*100).'"  stroke="dimgrey" stroke-width="4" />'."\n";

    }


// ***** X-Axis and Y-Axis *****
    if (!$noaxes) {
      $output .= '<line x1="0" y1="'.yi2o(0).'" x2="'.($nxgrids*100).'" y2="'.yi2o(0).'"  stroke="black" stroke-width="8" marker-end="url(#bigArrow)" />'."\n";
      $output .= '<line x1="'.xi2o(0).'" y1="'.($nygrids*100).'" x2="'.xi2o(0).'" y2="0" stroke="black" stroke-width="8" marker-end="url(#bigArrow)" />'."\n";
    }

// ***** Labeling of X-Axis and Y-Axis *****

    if (!$nolabels) {
      $output .= '<text x="'.(xi2o($xparam[1]+$xparam[2])+$xlabeloffset+80).'" y="'.(yi2o(0)+50).'" font-size="'.$fontsize.'" text-anchor="end" font-weight="bold" fill="black">'.$xlabel.'</text>'."\n";
      $output .= '<text x="'.(xi2o(0)+30).'" y="'.(yi2o($yparam[1])-60).'" font-size="'.$fontsize.'" text-anchor="start" font-weight="bold" fill="black">'.$ylabel.'</text>'."\n";
      $output .= '<text x="'.(xi2o(0)-20).'" y="'.(yi2o(0)+40).'" font-size="'.$fontsize.'" text-anchor="end" font-weight="bold" fill="black">0</text>'."\n";
    }

    if ( (!$nolabels) && (!$nolabelnums) ) {
      $c = 0;
      $xskip = 0; if ( isset($xparam[3]) ) $xskip = $xparam[3];
      for ($i=$xparam[0];$i<=$xparam[1];$i+=$xparam[2]) {
        if ( ($i!=0) && ($c%($xskip+1)==0) )
          $output .= '<text x="'.xi2o($i).'" y="'.(yi2o(0)+50).'" font-size="'.$fontsize.'" text-anchor="middle" font-weight="bold" fill="black">'.$i.'</text>'."\n";
        $c++;          
      }
      $c = 0;
      $yskip = 0; if ( isset($yparam[3]) ) $yskip = $yparam[3];
      for ($i=$yparam[0];$i<=$yparam[1];$i+=$yparam[2]) {
        if ( ($i!=0) && ($i%($yskip+1)==0) )
          $output .= '<text x="'.(xi2o(0)-20).'" y="'.(yi2o($i)+20).'" font-size="'.$fontsize.'" text-anchor="end" font-weight="bold" fill="black">'.$i.'</text>'."\n";
        $c++;
      }
    }


// ***** Plotting Curve *****
    for ($i=0;$i<$ncurve;$i++) {
      $output .= '<path stroke-width="4" stroke="'.$curvecolor[$i].'" fill="none" ';
      $output .= 'd="M '.xi2o($curvedata[$i][0]).','.yi2o($curvedata[$i][1]).' ';
      for ($j=1;$j<(count($curvedata[$i])-1)/2-1;$j++)
        $output .= QBezier($curvedata[$i][$j*2-2],$curvedata[$i][$j*2-1],$curvedata[$i][$j*2],$curvedata[$i][$j*2+1],$curvedata[$i][$j*2+2],$curvedata[$i][$j*2+3]);
//    $output .= 'T '.xi2o($curvedata[$i][$j*2]).' '.yi2o($curvedata[$i][$j*2+1]);
      $output .= QBezier2($curvedata[$i][$j*2-4],$curvedata[$i][$j*2-3],$curvedata[$i][$j*2-2],$curvedata[$i][$j*2-1],$curvedata[$i][$j*2],$curvedata[$i][$j*2+1]);
      $output .= '" />'."\n";
    }

// ***** Plotting Line *****
    for ($i=0;$i<$nline;$i++) {
      $output .= '<path stroke-width="4" stroke="'.$linecolor[$i].'" fill="none" ';
      $output .= 'd="M '.xi2o($linedata[$i][0]).','.yi2o($linedata[$i][1]).' ';
      for ($j=1;$j<(count($linedata[$i])-1)/2;$j++)
        $output .= 'L '.xi2o($linedata[$i][$j*2]).' '.yi2o($linedata[$i][$j*2+1]);
      $output .= '" />'."\n";
    }

// ***** Plotting Dash Line *****
    for ($i=0;$i<$ndash;$i++) {
      $output .= '<path stroke-width="4" stroke="'.$dashcolor[$i].'" fill="none" stroke-dasharray="15,15" ';
      $output .= 'd="M '.xi2o($dashdata[$i][0]).','.yi2o($dashdata[$i][1]).' ';
      for ($j=1;$j<(count($dashdata[$i])-1)/2;$j++)
        $output .= 'L '.xi2o($dashdata[$i][$j*2]).' '.yi2o($dashdata[$i][$j*2+1]);
      $output .= '" />'."\n";
    }

// ***** Plotting Points *****
    for ($i=0;$i<$npoints;$i++) {
      $output .= '<path stroke-width="6" stroke="'.$pointscolor[$i].'" fill="none" d=" ';
      for ($j=0;$j<(count($pointsdata[$i])-1)/2;$j++) {
        $output .= 'M '.xi2o($pointsdata[$i][$j*2]).','.yi2o($pointsdata[$i][$j*2+1]).' ';
        $output .= 'm -10 -10 l 20 20 m 0 -20 l -20 20 ';
      }
      $output .= '" />'."\n";
    }

// ***** Plotting Marks *****
    for ($i=0;$i<$nmark;$i++) {
      $output .= '<text x="'.(xi2o($markdata[$i][0])+$markparam[$i][1]).'" y="'.(yi2o($markdata[$i][1])+$markparam[$i][2]).'" font-size="'.$fontsize.'" fill="'.$markparam[$i][0].'">';
      $output .= $markdata[$i][2].'</text>'."\n";
    }

// ***** Plotting Equations *****
    for ($i=0;$i<$nequation;$i++) {
      if (count($equationdata[$i])==0) continue;
      $output .= '<path stroke-width="4" stroke="'.$equationcolor[$i].'" fill="none" ';
      $output .= 'd="M '.xi2o($equationdata[$i][0]).','.yi2o($equationdata[$i][1]).' ';
      for ($j=1;$j<(count($equationdata[$i])-1)/2;$j++)
        $output .= 'L '.xi2o($equationdata[$i][$j*2]).' '.yi2o($equationdata[$i][$j*2+1]);
      $output .= '" />'."\n";
    }


    $output .= '</svg>';

  }

  // Set the Content-Type header to SVG
  header('Content-Type: image/svg+xml');
  echo $output;
  exit;
  
?>
