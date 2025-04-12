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

$texdoc = 
'
\documentclass[12pt,a4paper]{article}


\usepackage{times}
\usepackage[utf8x]{inputenc} % needed for special characters
\usepackage[hmargin=3cm,vmargin={2.5cm,7.84cm},head=15pt]{geometry}
\usepackage{lastpage} % needed to work out total pages
\usepackage{parskip} % stops paragraph indents
\usepackage{fancyhdr} % needed for page headers and footers
\usepackage{multicol} % needed for multicolumn layout
\usepackage{enumitem} % needed for lists
\usepackage{pifont} % needed for Postscript dingbats
' . $watermark . '
\pagestyle{fancy}
\lfoot{{\small \itshape ~\\\\[1cm]~\\\\~\\\\~\\\\~\\\\ (\ding{45}) \rule{5cm}{0.2mm} \\\\ Signature of ' . $texOrderData['testatorFullName'] . ' \\\\~\\\\ \rule{10cm}{0.2mm} \\\\ Last Will and Testament of ' . $texOrderData['testatorFullName'] . ' }}
\cfoot{}
\rfoot{{\small \itshape ~\\\\[1cm] (\ding{45})\rule{5cm}{0.2mm} \\\\ Signature of Witness 1 \\\\~\\\\~\\\\ (\ding{45})\rule{5cm}{0.2mm} \\\\Signature of Witness 2 \\\\~\\\\ \rule{10cm}{0.2mm} \\\\ Page \thepage~of \pageref{LastPage}}}
\renewcommand{\headrulewidth}{0pt}
\renewcommand{\footrulewidth}{0pt}


\begin{document}
	%% Main body of will

	\begin{center}
  	\subsection*{Last Will and Testament of ' . $texOrderData['testatorFullName'] . '\\\\of ' . $texOrderData['testatorOneLineAddress'] . '}

		{\itshape Dated this \rule{2cm}{0.2mm} day of \rule{3cm}{0.2mm} , \rule{1.5cm}{0.2mm} \\\\[2cm]}
	\end{center}
	\begin{enumerate}[leftmargin=*]
		\item
		I, ' . $texOrderData['testatorFullName'] . ' (born on ' . textdate($texOrderData['testatorDOB']) . '), declare that this is my last will and testament and I hereby revoke, cancel and annul all wills, testamentary acts,
		dispositions and codicils previously made by me either jointly or alone. I declare that I am of legal age to make
		this will and of sound mind and that this last will and testament expresses my clear wishes without any
		undue influence or duress.
';

if (isset($texOrderData['executorFullName2'])) {
    # individual executors selected
  	$texdoc .= '
  		\item
  		I appoint as my Executor and Trustee of this my Will (hereinafter referred to as `My Trustee\'), the first person who is willing and able to act in the role from the nominees included within Clause 2.
  		The subsequent nominees will only act if there are no others willing or able to act who were mentioned before them in the list:
  		\begin{enumerate}[label=\roman*.]
  
			\item
			' . $texOrderData['executorFullName1'] . ' of ' . $texOrderData['executorOneLineAddress1'] . '.

			\item
			' . $texOrderData['executorFullName2'] . ' of ' . $texOrderData['executorOneLineAddress2'] . '.

   		\end{enumerate}

  	';
  	
/*
  	$texdoc .= '
  		If none of the nominated persons within clause 2 are willing or able
  		to act as Executors and Trustees of this my Will, then I appoint The Public Trustee in
  		' . $texOrderData['testatorState'] . ' to be Executor and Trustee of this my Will.
  
  	';
*/
} else {
	$texdoc .= '
		\item
		I appoint ' . $texOrderData['executorFullName1'] . ' of ' . $texOrderData['executorOneLineAddress1'] . ' to be Executor and Trustee
		(hereinafter referred to as `My Trustee\') of this my Will.
	';
	
/*
	$texdoc .= '
    If ' . $texOrderData['executorFullName1'] . ' is
		unwilling or unable to act in the capacity of Executor and Trustee, then I appoint
		The Public Trustee in ' . $texOrderData['testatorState'] . ' to be Executor and Trustee
		of this my Will.
	';
*/
}


$texdoc .= '
		\item
		My Trustee has all the power and authority to administer my estate that any person
		would have if they were the sole and absolute owner of the estate. My Trustee
		may do anything appropriate in administering my estate for the benefit of my beneficiaries.
';

if (isset($texOrderData['hasChildrenYoung']) && $texOrderData['hasChildrenYoung'] == 'Yes') {
  $plural = $texOrderData['guardianCouple'] ? 's' : '';
	$texdoc .= '
		\item
		If I have children under the age of 18 years at the time of my death, and these children have no surviving parent, then I appoint ' . $texOrderData['guardianFullName'] . ' of ' . $texOrderData['guardianOneLineAddress'] . ' as their guardian' . $plural . '.
	';	
		
	$texdoc .= '
		\item
		If a beneficiary mentioned in this my will, is under the age of 18 years
		at the time of my death, then the gift or share of my estate that they will receive according
		to this my Will, will be held in trust for them by My Trustee for the purpose of support,
		welfare and education until the beneficiary reaches the age of 18 years.

	';
}
		
if (count($texOrderData['specificGifts']) > 0) {
	$texdoc .= '
		\item
		I direct My Trustee to give the following specific gifts to my beneficiaries:
		\begin{enumerate}[label=\roman*.]
	';
	
	foreach($texOrderData['specificGifts'] as $gift) {
		$texdoc .= '
			\item
			' . $gift . '
			';
	}
	$texdoc .= '
		\end{enumerate}
	';
}
		
if (count($texOrderData['estate']) > 0) {
	$texdoc .= '
		\item
		I direct My Trustee to pay any outstanding funeral, probate and testamentary expenses, taxes and duties,
		and to pay all my just debts from the remainder of my estate (the residuary). I direct My Trustee to pay
		the balance of my residuary, after the repayment of the aforementioned expenses, taxes, duties and debts,
		to my beneficiaries as follows:
		\begin{enumerate}[label=\roman*.]
	';
	
	foreach($texOrderData['estate'] as $estate) {
  	$relationship = isset($estate['relationship']) ? 'my ' . $estate['relationship'] . ' ' : '';
		$texdoc .= '
			\item
			I give a share of ' . $estate['share'] . ' of my residuary to ' . $relationship . $estate['recipient'] . '.
			';

		$texdoc .= '
			' . $estate['altrecipient'] . '
			';
	}
	$texdoc .= '
		\end{enumerate}
	';
} else {
	$texdoc .= '
		\item
		{\itshape DISTRIBUTION OF ESTATE - YOU HAVE NOT YET MADE ANY DISTRIBUTION OF YOUR ESTATE - PLEASE GO TO THE "DISTRIBUTION OF ESTATE" SECTION AND ALLOCATE YOUR ESTATE}
	';
}

		
if ($texOrderData['requirements'] > 0) {
	$texdoc .= '
		\item
		I wish to have the following personal requirements carried out after my death:
		\begin{enumerate}[label=\roman*.]
	';
	
	foreach($texOrderData['requirements'] as $requirement) {
		$texdoc .= '
			\item
			' . $requirement . '
			';
	}
	$texdoc .= '
		\end{enumerate}
	';
}

		
$texdoc .= '		
		\item
		This, My Will, is governed by and to be interpreted in accordance with, the applicable laws
		of ' . $texOrderData['testatorState'] . ' (which is the place of my residing at the time of signing this My Will).
		
	\end{enumerate}
';

$texdoc .= '	
	%% Signature page
	\newpage
	\newgeometry{hmargin=3cm,vmargin=2.5cm}
	\renewcommand{\headrulewidth}{0pt}
	\renewcommand{\footrulewidth}{0.2mm}
	\lfoot{{\small \itshape Last Will and Testament of ' . $texOrderData['testatorFullName'] . '}}
	\rfoot{{\small \itshape Page \thepage~of \pageref{LastPage}}}

	\begin{center}
		{\itshape Dated this \rule{2cm}{0.2mm} day of \rule{3cm}{0.2mm} , \rule{1.5cm}{0.2mm} \\\\[2cm]}
	\end{center}

	\vspace{1.5cm}

	\begin{multicols}{2}
		\raggedright {\bfseries Signed by ' . $texOrderData['testatorFullName'] . '\\\\	{\small \itshape(the testator)}}:
		
		\columnbreak
		
		\rule{7cm}{0.2mm}\\\\	{\small \itshape (\ding{45}) Signature of testator}
	\end{multicols}
	
	\vspace{1.5cm}

	{\bfseries Attestation by witnesses:}

	Signed by the above testator in the presence of both of us the Witnesses being present
	at the same time and signed by each of us in the presence of each other and the testator.
	
	\vspace{2cm}

	\begin{multicols}{2}
		
		\hspace{1.5cm} {\bfseries Witness 1}	\\\\[2cm]
		\rule{7cm}{0.2mm}\\\\	{\small \itshape (\ding{45}) Signature of Witness 1} \\\\[1cm]
		\rule{7cm}{0.2mm}\\\\	{\small \itshape Print Name of Witness 1} \\\\[1cm]
		\rule{7cm}{0.2mm}\\\\[0.5cm]
		\rule{7cm}{0.2mm}\\\\[0.5cm]
		\rule{7cm}{0.2mm}\\\\[0.5cm]
		\rule{7cm}{0.2mm}\\\\ {\small \itshape Print Address of Witness 1}

		\columnbreak
		
		\hspace{1.5cm} {\bfseries Witness 2}	\\\\[2cm]
		\rule{7cm}{0.2mm}\\\\	{\small \itshape (\ding{45}) Signature of Witness 2} \\\\[1cm]
		\rule{7cm}{0.2mm}\\\\	{\small \itshape Print Name of Witness 2} \\\\[1cm]
		\rule{7cm}{0.2mm}\\\\[0.5cm]
		\rule{7cm}{0.2mm}\\\\[0.5cm]
		\rule{7cm}{0.2mm}\\\\[0.5cm]
		\rule{7cm}{0.2mm}\\\\ {\small \itshape Print Address of Witness 2}
	\end{multicols}

\end{document}

';
?>