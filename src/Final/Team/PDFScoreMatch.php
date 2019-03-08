<?php
	require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
	require_once('Common/pdf/ResultPDF.inc.php');
	require_once('Common/Fun_FormatText.inc.php');
	require_once('Common/Lib/ArrTargets.inc.php');
	require_once('Common/Lib/Fun_PrintOuts.php');

	$pdf = new ResultPDF((get_text('IndFinal')),false);
	$pdf->setBarcodeHeader(70);

	$CellH=12;
	$GoldW  = ($pdf->GetPageWidth()-3*$pdf->getSideMargin())/2*(1/18);
	$ArrowTotW = ($pdf->GetPageWidth()-3*$pdf->getSideMargin())/2*(9/18);
	$TotalW = ($pdf->GetPageWidth()-3*$pdf->getSideMargin())/2*(3/18);
	$GridTotH= $pdf->GetPageheight()*0.25;

	$FillWithArrows=false;
	if((isset($_REQUEST["ScoreFilled"]) && $_REQUEST["ScoreFilled"]==1))
		$FillWithArrows=true;

	$pdf->PrintFlags=(!empty($_REQUEST["ScoreFlags"]));

	$NumColStd = 6;
	$NumColField = 3;
	$StdCols=1;
	$NumRow=0;
	$Fita3D=false;
	/*$Select
		= "SELECT (TtElabTeam=0) as StdTournament, (TtCategory=8) as 3DTournament "
		. "FROM Tournament INNER JOIN Tournament*Type ON ToType=TtId "
		. "WHERE ToId=" . StrSafe_DB($_SESSION['TourId']) . " ";*/
	$Select
		= "SELECT (ToElabTeam=0) as StdTournament, (ToCategory=8) as 3DTournament "
		. "FROM Tournament "
		. "WHERE ToId=" . StrSafe_DB($_SESSION['TourId']) . " ";
	$RsTour=safe_r_sql($Select);
	if (safe_num_rows($RsTour)==1)
	{
		$r=safe_fetch($RsTour);
		$StdCols=$r->StdTournament;
		$Fita3D=$r->{'3DTournament'};
		safe_free_result($RsTour);
	}

	$MyQuery="";
	if (isset($_REQUEST['Blank']))
	{
		$_REQUEST["IncEmpty"]=true;
		$rows= empty($_REQUEST['Rows'])?5:intval($_REQUEST['Rows']);
		$cols= empty($_REQUEST['Cols'])?3:intval($_REQUEST['Cols']);
		$sots= empty($_REQUEST['SO'])?1:intval($_REQUEST['SO']);
		$MyQuery = "(SELECT DISTINCT "
			. " '' AS EvCode, '' AS EvEventName, '' AS EvFinalFirstPhase, 0 as EvMixedTeam, '' AS GrPhase, "
			. " '' AS TfTarget, 0 AS GrMatchNo, "
			. " '' AS CoCode, '' AS TeamName, '' AS TeRank, '' AS FSTarget, "
			. " '' AS TfArrowString, '' AS TfTieBreak, EvMatchMode, IF(EvMatchArrowsNo=0,0,1) AS EvMatchArrowsNo, 0 as Score, 0 as TfTie"
			. " , '$rows' CalcEnds "
			. " , '$cols' CalcArrows "
			. " , '$sots' CalcSO "
			. "FROM Events "
			. "WHERE EvTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND EvTeamEvent=1) "
			. " UNION ALL "
			. "(SELECT DISTINCT "
			. " '' AS EvCode, '' AS EvEventName, '' AS EvFinalFirstPhase, 0 as EvMixedTeam, '' AS GrPhase, "
			. " '' AS TfTarget, 1 AS GrMatchNo, "
			. " '' AS CoCode, '' AS TeamName, '' AS TeRank, '' AS FSTarget, "
			. " '' AS TfArrowString, '' AS TfTieBreak, EvMatchMode, IF(EvMatchArrowsNo=0,0,1) AS EvMatchArrowsNo, 0 as Score, 0 as TfTie"
			. " , '$rows' CalcEnds "
			. " , '$cols' CalcArrows "
			. " , '$sots' CalcSO "
			. "FROM Events "
			. "WHERE EvTournament=" . StrSafe_DB($_SESSION['TourId']) . " AND EvTeamEvent=1) "
			. "ORDER BY  EvMatchMode, EvMatchArrowsNo, GrMatchNo";
	}
	else
	{
		$MyQuery = 'SELECT '
			. ' EvCode, EvEventName, EvFinalFirstPhase, EvMixedTeam, GrPhase, '
			. ' GrMatchNo, EvMatchMode, EvMatchArrowsNo, '
			. " CoCode, CONCAT(CoName, IF(TfSubTeam>'1',CONCAT(' (',TfSubTeam,')'),'')) AS TeamName, TeRank, FSTarget, "
			. ' TfArrowString, TfTieBreak, TfSetScore, '
			. ' IF(EvMatchMode=0,TfScore,TfSetScore) AS Score, TfTie, @elimination:=pow(2, ceil(log2(GrPhase))+1) & EvMatchArrowsNo '
			. " , if(@elimination, EvElimEnds, EvFinEnds) CalcEnds "
			. " , if(@elimination, EvElimArrows, EvFinArrows) CalcArrows "
			. " , if(@elimination, EvElimSO, EvFinSO) CalcSO "
			. ' FROM Events'
			. ' INNER JOIN TeamFinals ON EvCode=TfEvent AND EvTournament=TfTournament'
			. ' INNER JOIN Grids ON TfMatchNo=GrMatchNo AND GrPhase<=EvFinalFirstPhase'
			. ' LEFT JOIN Teams ON TfTeam=TeCoId AND TfSubTeam=TeSubTeam AND TfTournament=TeTournament AND TfEvent=TeEvent and TeFinEvent=1 '
			. ' LEFT JOIN Countries on TfTeam=CoId AND TfTournament=CoTournament'
			. ' LEFT JOIN FinSchedule ON TfEvent=FSEvent AND TfMatchNo=FSMatchNo AND TfTournament=FSTournament AND FSTeamEvent=\'1\''
			. ' WHERE EvTournament=' . StrSafe_DB($_SESSION['TourId']) . ' AND EvTeamEvent=1 AND (FSTeamEvent=\'1\' || FSTeamEvent IS NULL) ';
			if (isset($_REQUEST['x_Session']) && $_REQUEST['x_Session']!=-1) {
				$MyQuery .= "AND concat(FSTeamEvent, FSScheduledDate, ' ', FSScheduledTime)=" . strSafe_DB($_REQUEST['x_Session']) . " ";
			} else {
				if (!empty($_REQUEST['Event'])) $MyQuery.= CleanEvents($_REQUEST['Event'], "EvCode") . " ";
				if (isset($_REQUEST['Phase']) && preg_match("/^[0-9]{1,2}$/i",$_REQUEST["Phase"])) $MyQuery.= "AND GrPhase = " . StrSafe_DB($_REQUEST['Phase']) . " ";
			}
			$MyQuery .= ' ORDER BY EvCode, GrPhase DESC, TfMatchNo ASC';
	 }

	$Rs=safe_r_sql($MyQuery);
// Se il Recordset è valido e contiene almeno una riga
	if (safe_num_rows($Rs)>0)
	{
		$WhereStartX=array($pdf->getSideMargin(),($pdf->GetPageWidth()+$pdf->getSideMargin())/2);
		$WhereStartY=array(60,60);
		$WhereX=NULL;
		$WhereY=NULL;
		$AtlheteName=NULL;
		//$NumRow=4;
		$FollowingRows=false;
//DrawScore
		while($MyRow=safe_fetch($Rs))
		{
			$MyRowOpp=safe_fetch($Rs);

			if(empty($_REQUEST["IncEmpty"]) and (!$MyRow->CoCode or !$MyRowOpp->CoCode)) {
				// se è vuoto uno dei due arcieri e non è selezionata l'inclusione
				// salta al prossimo record
				continue;
			}

			// disegna lo score di sinistra
			DrawScore($pdf, $MyRow, $MyRowOpp);

			// Disegna lo score di destra
			DrawScore($pdf, $MyRowOpp, $MyRow);

			// print barcode if any
			if(!empty($_REQUEST['Barcode'])) {
				$pdf->setxy($pdf->BarcodeHeaderX, 5);
				$pdf->SetFont('barcode','',25);
				$pdf->Cell($pdf->BarcodeHeader, 15, '*' . mb_convert_encoding($MyRow->GrMatchNo.'-1-'.$MyRow->EvCode, "UTF-8","cp1252") . "*",0,1,'C',0);
				$pdf->SetFont($pdf->FontStd,'',10);
				$pdf->setxy($pdf->BarcodeHeaderX, 16);
				$pdf->Cell($pdf->BarcodeHeader, 4, mb_convert_encoding($MyRow->GrMatchNo.'-1-'.$MyRow->EvCode, "UTF-8","cp1252"),0,1,'C',0);
			} else {
				$pdf->setBarcodeHeader(10);
			}

			if(!empty($_REQUEST['QRCode'])) {
				foreach($_REQUEST['QRCode'] as $k => $Api) {
					require_once('Api/'.$Api.'/DrawQRCode.php');
					$Function='DrawQRCode_'.preg_replace('/[^a-z0-9]/sim', '_', $Api);
					$Function($pdf, $pdf->BarcodeHeaderX -(25 * ($k+1)), 5, $MyRow->EvCode, $MyRow->GrMatchNo, $MyRow->GrPhase, 0, "MT");
				}
			}
		}
//END OF DrawScore
		safe_free_result($Rs);
	}

$pdf->Output();



function DrawScore(&$pdf, $MyRow, $MyRowOpp) {
	global $CFG,$GridTotH,$CellH, $GoldW, $ArrowTotW, $TotalW, $NumRow, $StdCols, $NumColStd, $NumColField, $Fita3D, $FollowingRows, $WhereStartX, $WhereStartY, $FillWithArrows;

	$NumColStd=$MyRow->CalcArrows;

	$NumCol = ($StdCols == 1 ? $NumColStd : $NumColField);
	$NumRows = $MyRow->CalcEnds; // ($Fita3D == 1  ? 8 : $MyRow->CalcEnds);
	$ColWidth = $ArrowTotW / $MyRow->CalcArrows;

	$TmpCellH = $GridTotH/$NumRows;

	if($MyRow->GrMatchNo%2 == 0 && $FollowingRows)
		$pdf->AddPage();

	$FollowingRows=true;
	$WhichScore=($MyRow->GrMatchNo%2);
	$WhereX=$WhereStartX;
	$WhereY=$WhereStartY;
//Intestazione Atleta
	$pdf->SetLeftMargin($WhereStartX[$WhichScore]);
	$pdf->SetY(35);
// Flag of Country/Club
	if($pdf->PrintFlags) {
		if(is_file($file= $CFG->DOCUMENT_PATH.'TV/Photos/'.$_SESSION['TourCodeSafe'].'-Fl-'.$MyRow->CoCode.'.jpg')) {
			$H=12;
			$W=18;
			$OrgY=$pdf->gety();
			$OrgX=$ArrowTotW+2*$TotalW+$GoldW-18;
			$pdf->Image($file, $pdf->getx()+$OrgX, $OrgY, $W, $H, 'JPG', '', '', true, 300, '', false, false, 1, true);
			$FlagOffset=$W+1;
		}
	}

   	$pdf->SetFont($pdf->FontStd,'',10);
	$pdf->Cell(20, 12,(get_text('Country')) . ': ', 'LT', 0, 'L', 0);
	$pdf->SetFont($pdf->FontStd,'B',10);
	$pdf->Cell($ArrowTotW+2*$TotalW+$GoldW-20, 12, ($MyRow->TeamName . (strlen($MyRow->CoCode)>0 ?  ' (' . $MyRow->CoCode  . ')' : '')), 'T', 1, 'L', 0);
	$pdf->SetFont($pdf->FontStd,'',10);
	$pdf->Cell(20,6,(get_text('DivisionClass')) . ': ', 'LB', 0, 'L', 0);
	$pdf->SetFont($pdf->FontStd,'B',10);
	$pdf->Cell($ArrowTotW+$TotalW+$GoldW-20,6, get_text($MyRow->EvEventName,'','',true), 'B', 0, 'L', 0);
	$pdf->SetFont($pdf->FontStd,'B',8);
	$pdf->Cell($TotalW, 6, (get_text('Target')) . ' ' . $MyRow->FSTarget, '1', 1, 'C', 1);

	$pdf->SetXY($ArrowTotW+2*$TotalW+$GoldW+$WhereStartX[$WhichScore],35);
	$pdf->SetFont($pdf->FontStd,'B',10);
	$pdf->Cell(2*$GoldW,6, (get_text('Rank')),'TLR',1,'C',1);
	$pdf->SetXY($ArrowTotW+2*$TotalW+$GoldW+$WhereStartX[$WhichScore],$pdf->GetY());
	$pdf->SetFont($pdf->FontStd,'B',25);
	$pdf->Cell(2*$GoldW,12, $MyRow->TeRank,'BLR',1,'C',1);
//Header
   	$pdf->SetFont($pdf->FontStd,'B',10);
	$pdf->SetXY($WhereX[$WhichScore],$WhereY[$WhichScore]);
	$pdf->Cell($GoldW,$CellH,'',0,0,'C',0);
	$pdf->Cell(2*$GoldW+2*$TotalW+$ArrowTotW,$CellH,$MyRow->GrPhase!==''?get_text($MyRow->GrPhase . '_Phase'):'',1,0,'C',1);
//	$WhereY[$WhichScore]=$pdf->GetY();
//Winner Checkbox
	$pdf->SetXY($WhereX[$WhichScore],$WhereY[$WhichScore]);
	$pdf->Rect($WhereX[$WhichScore]+$GoldW+2,$WhereY[$WhichScore]+2,$ColWidth-4,$CellH-4,'DF',array(),array(255,255,255));
	if($FillWithArrows && ($MyRow->Score > $MyRowOpp->Score || ($MyRow->Score == $MyRowOpp->Score && $MyRow->TfTie > $MyRowOpp->TfTie )))
	{
		$tmpWidth=$pdf->GetLineWidth();
		$pdf->SetLineWidth($tmpWidth*5);
		$pdf->Line($WhereX[$WhichScore]+$GoldW+1,$WhereY[$WhichScore]+1,$WhereX[$WhichScore]+$GoldW+$ColWidth-1,$WhereY[$WhichScore]+$CellH-1);
		$pdf->Line($WhereX[$WhichScore]+$GoldW+1,$WhereY[$WhichScore]+$CellH-1,$WhereX[$WhichScore]+$GoldW+$ColWidth-1,$WhereY[$WhichScore]+1);
		$pdf->SetLineWidth($tmpWidth);
	}
	$pdf->SetDefaultColor();
	$pdf->Cell($GoldW+$ColWidth,$CellH,'',0,0,'C',0);
	$pdf->Cell(2*$GoldW+2*$TotalW+($NumCol-1)*$ColWidth,$CellH, get_text('Winner'),0,1,'L',0);
	$WhereY[$WhichScore]=$pdf->GetY();

// Row 2: Arrow numbers, Gold, Xs, Sto points, etc
	$pdf->SetXY($WhereX[$WhichScore],$WhereY[$WhichScore]);
	$pdf->Cell($GoldW,$CellH,'',0,0,'C',0);
	for($j=0; $j<$MyRow->CalcArrows; $j++)
		$pdf->Cell($ColWidth,$CellH, ($j+1), 1, 0, 'C', 1);

	$pdf->Cell($TotalW * ($MyRow->EvMatchMode==0 ? 1:4/5),$CellH, get_text(($MyRow->EvMatchMode==0 ? 'TotalProg':'SetTotal'),'Tournament'),1,0,'C',1);
	$pdf->Cell($TotalW * ($MyRow->EvMatchMode==0 ? 1:4/5),$CellH, get_text('TotalShort','Tournament'),1,0,'C',1);

	if($MyRow->EvMatchMode==0)
	{
		$pdf->Cell($GoldW,$CellH,($pdf->prnGolds),1,0,'C',1);
		$pdf->Cell($GoldW,$CellH,($pdf->prnXNine),1,1,'C',1);
	}
	else
	{
		$pdf->Cell(2*$GoldW,$CellH,get_text('SetPoints', 'Tournament'),1,0,'C',1);
		$pdf->Cell(2/5*$TotalW,$CellH,get_text('TotalShort','Tournament'),1,1,'C',1);
	}
	$WhereY[$WhichScore]=$pdf->GetY();
//Righe
	$ScoreTotal = 0;
	$ScoreGold = 0;
	$ScoreXnine = 0;
	$SetTotal = '';
	for($i=1; $i<=$NumRows; $i++)
	{
		$ScoreEndTotal = 0;
		$ScoreEndGold = 0;
		$ScoreEndXnine = 0;
		$pdf->SetFont($pdf->FontStd,'B',10);
		$pdf->SetXY($WhereX[$WhichScore],$WhereY[$WhichScore]);
		$pdf->Cell($GoldW,$TmpCellH, (($Fita3D || !$StdCols)  && $MyRow->FSTarget ? ((intval($MyRow->FSTarget)+$i-2)%$NumRows)+1 : $i),1,0,'C',1);
		$pdf->SetFont($pdf->FontStd,'',10);
		for($j=0; $j<$MyRow->CalcArrows; $j++)
			$pdf->Cell($ColWidth,$TmpCellH,($FillWithArrows ? DecodeFromLetter(substr($MyRow->TfArrowString,($i-1)*$NumCol+$j,1)) : ''),1,0,'C',0);
		$IsEndScore= trim(substr($MyRow->TfArrowString, ($i-1)*$NumCol, $NumCol));
		list($ScoreEndTotal,$ScoreEndGold, $ScoreEndXnine) = ValutaArrowStringGX(substr($MyRow->TfArrowString,($i-1)*$NumCol,$NumCol),$pdf->goldsChars,$pdf->xNineChars);
		$ScoreTotal += $ScoreEndTotal;
		$ScoreGold += $ScoreEndGold;
		$ScoreXnine += $ScoreEndXnine;

		$pdf->SetFont($pdf->FontStd,'', ($MyRow->EvMatchMode==0 ? 10 : 12));
		$pdf->Cell($TotalW * ($MyRow->EvMatchMode==0 ? 1:4/5),$TmpCellH,($FillWithArrows && $IsEndScore ? $ScoreEndTotal : ''),1,0,'C',0);
		$pdf->SetFont($pdf->FontStd,'', ($MyRow->EvMatchMode==0 ? 12 : 10));
		$pdf->Cell($TotalW* ($MyRow->EvMatchMode==0 ? 1:4/5),$TmpCellH,($FillWithArrows && $IsEndScore ? $ScoreTotal : ''),1,0,'C',0);
		if($MyRow->EvMatchMode==0) {
			$pdf->Cell($GoldW,$TmpCellH,($FillWithArrows && strlen(substr($MyRow->TfArrowString,($i-1)*$NumCol,$j))? $ScoreEndGold : ''),1,0,'C',0);
			$pdf->Cell($GoldW,$TmpCellH,($FillWithArrows && strlen(substr($MyRow->TfArrowString,($i-1)*$NumCol,$j))? $ScoreEndXnine : ''),1,1,'C',0);
		} else {
			$SetTotSx = '';
			if($IsEndScore && $FillWithArrows) {
				$SetPointSx= ValutaArrowString(substr($MyRow->TfArrowString, ($i-1)*$NumCol, $NumCol));
				$SetPointDx= ValutaArrowString(substr($MyRowOpp->TfArrowString, ($i-1)*$NumCol, $NumCol));

				if($SetPointSx > $SetPointDx) {
					$SetTotSx= 2;
				} elseif($SetPointSx < $SetPointDx) {
					$SetTotSx= 0;
				} else {
					$SetTotSx= 1;
				}
				$SetTotal = intval($SetTotal) + $SetTotSx;
			}

			$pdf->SetFont($pdf->FontStd,'B',11);
			if($SetTotSx==2 && $FillWithArrows)
				$pdf->Circle($pdf->GetX()+$GoldW/3,$pdf->GetY()+$TmpCellH/2, $GoldW/3, 0, 360, 'FD');
			$pdf->Cell((2*$GoldW)/3,$TmpCellH,'2',1, 0,'C',0);
			if($SetTotSx==1 && $FillWithArrows)
				$pdf->Circle($pdf->GetX()+$GoldW/3,$pdf->GetY()+$TmpCellH/2, $GoldW/3, 0, 360, 'FD');
			$pdf->Cell((2*$GoldW)/3,$TmpCellH,'1',1, 0,'C',0);
			if($SetTotSx==0 && $IsEndScore && $FillWithArrows)
				$pdf->Circle($pdf->GetX()+$GoldW/3,$pdf->GetY()+$TmpCellH/2, $GoldW/3, 0, 360, 'FD');
			$pdf->Cell((2*$GoldW)/3,$TmpCellH,'0',1, 0,'C',0);
			$pdf->Cell($TotalW * 2/5,$TmpCellH,($IsEndScore && $FillWithArrows ? $SetTotal : ''),1, 1,'C',0);
		}
		$WhereY[$WhichScore]=$pdf->GetY();
	}
//Tie Break
	$closeToCenter=false;
	$pdf->SetXY($WhereX[$WhichScore],$WhereY[$WhichScore]+($CellH/4));
	$pdf->SetFont($pdf->FontStd,'B',8);
	$pdf->Cell($GoldW, $TmpCellH+1+$GoldW, (get_text('TB')),1,0,'C',1);
	$pdf->SetFont($pdf->FontStd,'',10);
	for($j=0; $j<$MyRow->CalcSO; $j++)
	{
		$pdf->Cell($ArrowTotW/$MyRow->CalcSO, $TmpCellH,($FillWithArrows ? DecodeFromLetter(substr($MyRow->TfTieBreak,$j,1)) : ''),1,0,'C',0);
		if(substr(($FillWithArrows ? DecodeFromLetter(substr($MyRow->TfTieBreak,$j,1)) : ''),-1,1)=="*") {
			$closeToCenter=true;
		}
	}
	if($MyRow->TfTie==1) $SetTotal+=1;
	$SOY=$pdf->GetY();

//Totale
	$Errore=($FillWithArrows and (strlen($MyRow->TfArrowString) and ($MyRow->EvMatchMode ? $MyRow->Score!=$SetTotal : $MyRow->Score!=$ScoreTotal)));
	$pdf->SetXY($TopX=$pdf->GetX(), $WhereY[$WhichScore]);
	$pdf->SetFont($pdf->FontStd,'B',10);
	if($MyRow->EvMatchMode==0) {
		$pdf->Cell($TotalW,$TmpCellH,(get_text('Total')),0,0,'R',0);
		$pdf->SetFont($pdf->FontStd,'B',12);
		$pdf->Cell($TotalW,$TmpCellH,($FillWithArrows && strlen($MyRow->TfArrowString)? $ScoreTotal : ''),1,0,'C',0);
		if($Errore) {
			$pdf->Line($x1 = $pdf->getx() - $TotalW, $y1=$pdf->gety()+$CellH, $x1+$TotalW, $y1-$CellH);
		}
		$pdf->SetFont($pdf->FontStd,'',12);
		$pdf->Cell($GoldW,$TmpCellH,($FillWithArrows && strlen($MyRow->TfArrowString)? $ScoreGold : ''),1,0,'C',0);
		$pdf->Cell($GoldW,$TmpCellH,($FillWithArrows && strlen($MyRow->TfArrowString)? $ScoreXnine : ''),1,1,'C',0);
	} else {
		$pdf->Cell($TotalW * 8/5,$TmpCellH,'',0,0,'R',0);
		$pdf->SetFont($pdf->FontStd,'B',10);
		$pdf->Cell(2*$GoldW,$TmpCellH,get_text('Total'),0,0,'R',0);
		$pdf->SetFont($pdf->FontStd,'B',14);
		$pdf->Cell(2/5*$TotalW,$TmpCellH,($FillWithArrows ? $MyRow->TfSetScore : ''),1,1,'C',0);
		if($Errore) {
			$pdf->Line($x1 = $pdf->getx() - 2/5*$TotalW, $y1=$pdf->gety()+$TmpCellH, $x1 + 2/5*$TotalW, $y1-$TmpCellH);
		}
	}

	if($Errore) {
		$pdf->SetX($TopX);
		$pdf->SetFont($pdf->FontStd,'B',11);
		$pdf->Cell($MyRow->EvMatchMode ? 2*$GoldW + $TotalW * 8/5 : $TotalW, $CellH, (get_text('SignedTotal', 'Tournament') . " "), 0,0,'R',0);
		$pdf->Cell($MyRow->EvMatchMode ? 2/5*$TotalW : $TotalW, $CellH, $MyRow->Score, 1, 0, 'C', 0);
		$pdf->ln();
	}

//Closet to the center
	$pdf->SetFont($pdf->FontStd,'',9);
	$pdf->SetXY($WhereX[$WhichScore]+$GoldW, $SOY + $TmpCellH + 1);
	$pdf->Cell($ColWidth,$GoldW,'',1,0,'R',0);
	if($closeToCenter) {
		$tmpWidth=$pdf->GetLineWidth();
		$pdf->SetLineWidth($tmpWidth*5);
		$pdf->Line($WhereX[$WhichScore]+$GoldW,$SOY + $TmpCellH + 1, $WhereX[$WhichScore]+$GoldW+$ColWidth, $SOY + $TmpCellH + 1 + $GoldW);
		$pdf->Line($WhereX[$WhichScore]+$GoldW,$SOY + $TmpCellH + 1 + $GoldW, $WhereX[$WhichScore]+$GoldW+$ColWidth, $SOY + $TmpCellH + 1);
		$pdf->SetLineWidth($tmpWidth);
	}
	$pdf->Cell($ColWidth*($NumCol-1),$CellH*2/4,get_text('Close2Center','Tournament'),0,0,'L',0);
	$WhereY[$WhichScore]=$pdf->GetY()+10;
//Firme
	$pdf->SetXY($WhereX[$WhichScore],$WhereY[$WhichScore]);
   	$pdf->SetFont($pdf->FontStd,'I',7);
	$pdf->Cell($ColWidth + $GoldW ,4,(get_text('Archer')),'B',0,'L',0);
	$pdf->Cell(($NumCol-1)*$ColWidth + 2*($TotalW + $GoldW),4,'','B',1,'L',0);
	$WhereY[$WhichScore]=$pdf->GetY()+6;
	$pdf->SetXY($WhereX[$WhichScore],$WhereY[$WhichScore]);
	$pdf->Cell($ColWidth + $GoldW ,4,(get_text('Scorer')),'B',0,'L',0);
	$pdf->Cell(($NumCol-1)*$ColWidth + 2*($TotalW + $GoldW),4,'','B',1,'L',0);
	$WhereY[$WhichScore]=$pdf->GetY()+6;
	$pdf->SetXY($WhereX[$WhichScore],$WhereY[$WhichScore]);
	$pdf->Cell($ColWidth + $GoldW ,4,(get_text('JudgeNotes')),'B',0,'L',0);
	$pdf->Cell(($NumCol-1)*$ColWidth + 2*($TotalW + $GoldW),4,'','B',1,'L',0);
	$WhereY[$WhichScore]=$pdf->GetY();
}


?>
