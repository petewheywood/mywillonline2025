<?php
$watermark = '';
if (isset($preview) && $preview) {
  $watermark = '
\usepackage{xcolor}
\usepackage[printwatermark]{xwatermark}
\newwallpaper[%
  allpages,textcolor=red!10,fontsize=12pt,
  textalign=center,textangle=20,
  tilexoffset=1cm,tileyoffset=1cm,
  tilexsize=4cm,tileysize=0.5cm,
  boxalign=center,wpxoffset=0cm
]{Draft Only}
';
}

$texdoc = '
\documentclass[12pt,a4paper]{article}

\usepackage{times}
\usepackage[utf8x]{inputenc} % needed for special characters
\usepackage[hmargin=2.5cm,vmargin=2.5cm,head=15pt]{geometry}
\usepackage{lastpage} % needed to work out total pages
\usepackage{parskip} % stops paragraph indents
\usepackage{fancyhdr} % needed for page headers and footers
\usepackage{multicol} % needed for multicolumn layout
\usepackage{enumitem} % needed for lists
\usepackage{pifont} % needed for Postscript dingbats
' . $watermark . '
\pagestyle{fancy}
\nonstopmode


\lfoot{{\small \itshape Executor\'s Memorandum for ' . $texOrderData['person']['fullname'] . '}}
\cfoot{}
\rfoot{{\small \itshape Page \thepage~of \pageref{LastPage}}}
\renewcommand{\headrulewidth}{0pt}
\renewcommand{\footrulewidth}{0.25pt}

\begin{document}

	%% Main body of Executors Memo

	\begin{center}
		\section*{Executor\'s Memorandum}
	\end{center}
	
	This Executor\'s Memorandum is not part of the Last Will and Testament of ' . $texOrderData['person']['fullname'] . ',
	but is a separate document to assist the Executor with the administration of the estate.
	It contains personal and private information that should be kept confidential.
	This document contains names and addresses of beneficiaries of the estate, names and addresses of nominated guardians, optional pet care details, and possibly includes a list of
	assets and liabilities at the time of the creation of the will (' . $createDate . '), people to contact on the
	death of ' . $texOrderData['person']['fullname'] . ', and other personal information that would assist the Executor.
	
	\subsection*{Names and Addresses of Beneficiaries}
';

if ($texOrderData['hasSpouse'] == 'Yes') {
	$texdoc .= '
	\begin{tabular}{p{8cm} p{6cm}}
		\textbf{' . $texOrderData['spouse']['fullname'] . '} \textit{(' . ucwords($texOrderData['spouse']['relationship']) . ')} & ' . $texOrderData['spouse']['fulladdress'] . ' \\\\
		& \ding{37} ' . $texOrderData['spouse']['phone'] . ' \\\\
		& \ding{41} ' . $texOrderData['spouse']['email'] . ' \\\\~\\\\
	\end{tabular}
	';
}

if ($texOrderData['childCount'] > 0) {
	foreach($texOrderData['child'] as $child) {
		$texdoc .= '
	\begin{tabular}{p{8cm} p{6cm}}
		\textbf{' . $child['fullname'] . '} \textit{(' . ucwords($child['relationship']) . ')} & ' . $child['fulladdress'] . ' \\\\
		& \ding{37} ' . $child['phone'] . ' \\\\
		& \ding{41} ' . $child['email'] . ' \\\\~\\\\
	\end{tabular}
		';
	}
}

if ($texOrderData['otherCount'] > 0) {
	foreach($texOrderData['other'] as $other) {
		$texdoc .= '
	\begin{tabular}{p{8cm} p{6cm}}
		\textbf{' . $other['fullname'] . '} \textit{(' . ucwords($other['relationship']) . ')} & ' . $other['fulladdress'] . ' \\\\
		& \ding{37} ' . $other['phone'] . ' \\\\
		& \ding{41} ' . $other['email'] . ' \\\\~\\\\
	\end{tabular}
		';
	}
}

if ($texOrderData['groupCount'] > 0) {
	foreach($texOrderData['group'] as $group) {
		$texdoc .= '
	\begin{tabular}{p{8cm} p{6cm}}
		\textbf{' . $group['fullname'] . '} & ' . $group['fulladdress'] . ' \\\\
		& \ding{37} ' . $group['phone'] . ' \\\\
		& \ding{41} ' . $group['email'] . ' \\\\~\\\\
	\end{tabular}
		';
	}
}

if ($texOrderData['hasChildrenYoung'] == 'Yes') {
	$texdoc .= '
	\subsection*{Names and Addresses of Guardians of Minor Children}
	\begin{tabular}{p{8cm} p{6cm}}
	';
	foreach($texOrderData['guardian'] as $guardian) {
		if (isset($guardian['fullname']) && $guardian['fullname'] > '') {
			$texdoc .= '
		\textbf{' . $guardian['fullname'] . '} & ' . $guardian['fulladdress'] . ' \\\\
		& \ding{37} ' . $guardian['phone'] . ' \\\\
		& \ding{41} ' . $guardian['email'] . ' \\\\~\\\\
			';
		}
	}
	$texdoc .= '
	\end{tabular}
	';
}

if ($texOrderData['petCount'] > 0) {
  $isPetCareOrgs = false;
	foreach($texOrderData['pet'] as $pet) {
  	if (is_numeric($pet['carerrecipient'])) {
    	$isPetCareOrgs = true;
    	break;
    }
  }
  if ($isPetCareOrgs) {
    $petcarerIDs = array();
  	$texdoc .= '
  	\subsection*{Pet Legacy Program Providers}
  	\begin{tabular}{p{8cm} p{6cm}}
  	';
  	foreach($texOrderData['pet'] as $pet) {
  		if (isset($pet['carerfullname']) && $pet['carerfullname'] > '' && !in_array($pet['carerrecipient'], $petcarerIDs)) {
  			$texdoc .= '
  		\textbf{' . $pet['carerfullname'] . '} \textit{(ABN: ' . $pet['carerabn'] . ')} & ' . $pet['carerfulladdress'] . ' \\\\
  		& \ding{37} ' . $pet['carerphone'] . ' \\\\
  		& \ding{41} ' . $pet['careremail'] . ' \\\\~\\\\
  			';
  		}
    	$petcarerIDs[] = $pet['carerrecipient'];
  	}
  	$texdoc .= '
  	\end{tabular}
  	';
	}
}

if ($texOrderData['infoCount'] > 0) {
	$texdoc .= '
	\pagebreak
	\subsection*{Personal Information for ' . $texOrderData['person']['fullname'] . '}
	\begin{enumerate}
	';
	foreach($texOrderData['info'] as $info) {
		$texdoc .= '
		\item
		' . $info['text'] . '
		';
	}
	$texdoc .= '
	\end{enumerate}
	';
}

if ($texOrderData['assetCount'] > 0) {
	$texdoc .= '
	\subsection*{Asset List of ' . $texOrderData['person']['fullname'] . ' as at ' . $createDate . '}
	\begin{enumerate}
	';
	foreach($texOrderData['asset'] as $asset) {
		$texdoc .= '
		\item
		' . $asset['text'] . '
		';
	}
	$texdoc .= '
	\end{enumerate}
	';
}

if ($texOrderData['liabilityCount'] > 0) {
	$texdoc .= '
	\subsection*{Liability List of ' . $texOrderData['person']['fullname'] . ' as at ' . $createDate . '}
	\begin{enumerate}
	';
	foreach($texOrderData['liability'] as $liability) {
		$texdoc .= '
		\item
		' . $liability['text'] . '
		';
	}
	$texdoc .= '
	\end{enumerate}
	';
}

if ($texOrderData['messageCount'] > 0) {
	foreach($texOrderData['message'] as $message) {
		$texdoc .= '
		\pagebreak
		\subsection*{Personal Message from ' . $texOrderData['person']['fullname'] . ' to ' . $message['fullname'] . '}
		' . $message['text'] . '
		
		\textit{Dated: ' . $createDate . '}
		';
	}
}

if ($texOrderData['petCount'] > 0) {
	foreach($texOrderData['pet'] as $pet) {
		$texdoc .= '
		\pagebreak
		\subsection*{\underline{Pet Care Instructions for ' . $pet['name'] . '}}
		
		';
		
		$texdoc .= '
		  \textbf{Carer Details for ' . $pet['name'] . ':} \\\[0.2cm]
		';
		
		if ($pet['carerrecipient'] == 'Other') {
  		$texdoc .= 'It is my wish that my ' . $pet['type'] . ', ' . $pet['name'] . ', be cared for as specified in \emph{Pet Care Details} below.
  		';
    } else {
  		$texdoc .= 'It is my wish that my ' . $pet['type'] . ', ' . $pet['name'] . ', be cared for by ' . $pet['carerrelationship'] . ' ' . $pet['carerfullname'] . '.
  		';
    }
    
    if (isset($pet['amount'])) {
      $texdoc .= 'The amount of ' . $pet['amount'] . ' has been gifted in my will to ' . $pet['carerrelationship'] . ' ' . $pet['carerfullname'] . ' for the upkeep and care
      of ' . $pet['name'] . '. 
      ';
    }
		
		$texdoc .= '
		
		\textbf{About ' . $pet['name'] . ':} \\\[0.2cm]
		' . $pet['details'] . '
		
		\textbf{How ' . $pet['name'] . ' should be cared for:} \\\[0.2cm]
		' . $pet['caredetails'] . '
		';
		
		$texdoc .= '
		
		\textit{Dated: ' . $createDate . '}
		';
	}
}



$texdoc .= '

\end{document}

';

?>