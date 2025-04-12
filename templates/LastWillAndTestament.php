<?php
$watermark = '';
if (isset($preview) && $preview) {
  $watermark = '
\usepackage{xcolor}
\usepackage[printwatermark]{xwatermark}
\newwallpaper[%
  allpages,textcolor=black!20,fontsize=12pt,
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
\usepackage[utf8x]{inputenc}
\usepackage[hmargin=3cm,vmargin={2.5cm,7.84cm},head=15pt]{geometry}
\usepackage{lastpage} % needed to work out total pages
\usepackage{parskip} % stops paragraph indents
\usepackage{fancyhdr} % needed for page headers and footers
\usepackage{multicol} % needed for multicolumn layout
\usepackage{enumitem} % needed for lists
\usepackage{pifont} % needed for Postscript dingbats
' . $watermark . '
\pagestyle{fancy}
\nonstopmode

\lfoot{{\small \itshape ~\\\\[1cm]~\\\\~\\\\~\\\\~\\\\ (\ding{45}) \rule{5cm}{0.2mm} \\\\ Signature of ' . $texOrderData['person']['fullname'] . ' \\\\~\\\\ \rule{10cm}{0.2mm} \\\\ Last Will and Testament of ' . $texOrderData['person']['fullname'] . ' }}
\cfoot{}
\rfoot{{\small \itshape ~\\\\[1cm] (\ding{45})\rule{5cm}{0.2mm} \\\\ Signature of Witness 1 \\\\~\\\\~\\\\ (\ding{45})\rule{5cm}{0.2mm} \\\\Signature of Witness 2 \\\\~\\\\ \rule{10cm}{0.2mm} \\\\ Page \thepage~of \pageref{LastPage}}}
\renewcommand{\headrulewidth}{0pt}
\renewcommand{\footrulewidth}{0pt}


\begin{document}
  %% Title page
  \begin{titlepage}
    \begin{center}
      % Title
      ~\\\\[6cm]
      %%\rule{\linewidth}{0.3mm}\\\\[0.4cm]
      {\Huge \itshape \bfseries Last Will and Testament\\\\[0.4cm]of\\\\[0.4cm]
      ' . $texOrderData['person']['fullname'] . '
      \\\\[0.4cm]}
      %%\rule{\linewidth}{0.3mm}\\\\
      \vfill
      % Bottom of the page
      {\large \itshape Dated this \rule{2cm}{0.2mm} day of \rule{3cm}{0.2mm} , \rule{1.5cm}{0.2mm} }
    \end{center}
  \end{titlepage}

  %% Main body of will

  \subsection*{Last Will and Testament of ' . $texOrderData['person']['fullname'] . ' of\\\\' . $texOrderData['person']['onelineaddress'] . '}
  
  \begin{enumerate}[leftmargin=*]

    \item
    I declare that this is my last will and testament and I hereby revoke, cancel and annul all wills, testamentary acts,
    dispositions and codicils previously made by me either jointly or alone. I declare that I am of legal age to make
    this will and of sound mind and that this last will and testament expresses my clear wishes without any
    undue influence or duress.
';

if (isset($texOrderData['executorCount']) && $texOrderData['executorCount'] > 1) {
  if (isset($texOrderData['jointExecutors']) && $texOrderData['jointExecutors'] == 'Yes') {
    # joint executors selected
    $texdoc .= '
      \item
      I appoint the following as Joint Executors and Trustees (hereinafter referred to as `My Trustee\') of this my will:
      \begin{enumerate}[label=\roman*.]
  
    ';
    
    foreach($texOrderData['executor'] as $executor) {
      $texdoc .= '
        \item
        ' . $executor['fullname'] . ' of ' . $executor['onelineaddress'] . '. If ' . $executor['fullname'] . '
        is unwilling or unable to act in the capacity as a Joint Executor and Trustee, then the
        remaining Joint Executors and Trustees listed within clause 2 will fulfill the capacity
        of Joint Executors and Trustees.
  
      ';
    }
  } else {
    # individual executors selected
    $texdoc .= '
      \item
      I appoint as my Executor and Trustee of this my will (hereinafter referred to as `My Trustee\'), the first person who is willing and able to act in the role from the nominees included within Clause 2.
      The subsequent nominees will only act if there are no others willing or able to act who were mentioned before them in the list:
      \begin{enumerate}[label=\roman*.]
  
    ';
    
    foreach($texOrderData['executor'] as $executor) {
      $texdoc .= '
        \item
        ' . $executor['fullname'] . ' of ' . $executor['onelineaddress'] . '.
  
      ';
    }
  }
  $texdoc .= '
    \end{enumerate}
  ';

} else {
  $executor = isset($texOrderData['executor'][1]) ? $texOrderData['executor'][1] : '';  
  $texdoc .= '
    \item
    I appoint ' . $executor['fullname'] . ' of ' . $executor['onelineaddress'] . ' to be Executor and Trustee
    (hereinafter referred to as `My Trustee\') of this my will.
  ';
  
  if (isset($texOrderData['usePublicTrustee']) && $texOrderData['usePublicTrustee'] == 'Yes') {
    $texdoc .= '
      If ' . $executor['fullname'] . ' is
      unwilling or unable to act in the capacity of Executor and Trustee, then I appoint
      The Public Trustee in ' . $texOrderData['person']['fullstate'] . ' to be Executor and Trustee
      of this my will.
    ';
  }
}

if (isset($texOrderData['executorConditions']) && $texOrderData['executorConditions'] > ' ') {
    $texdoc .= '
    
    ' . $texOrderData['executorConditions'] . '
    
  ';

}

$texdoc .= '
    \item
    My Trustee has all the power and authority to administer my estate that any person
    would have if they were the sole and absolute owner of the estate. My Trustee
    may do anything appropriate in administering my estate for the benefit of my beneficiaries.
';

if (isset($texOrderData['hasChildrenYoung']) && $texOrderData['hasChildrenYoung'] == 'Yes') {
  if ($texOrderData['guardian'][1]['fullname'] == '') $texOrderData['guardian'][1]['fullname'] = 'GUARDIAN NAME';
  if ($texOrderData['guardian'][1]['onelineaddress'] == '') $texOrderData['guardian'][1]['onelineaddress'] = 'GUARDIAN ADDRESS';
  $texdoc .= '
    \item
    I appoint ' . $texOrderData['guardian'][1]['fullname'] . ' of ' . $texOrderData['guardian'][1]['onelineaddress'] . ' as the guardian of my minor children.
  ';
  
/*
  if ($texOrderData['hasSpouse'] == 'Yes') {
    $texdoc .= '
    This appointment will only take effect, if my spouse, ' . $texOrderData['spouse']['fullname'] . ', does not survive me,
    or is otherwise unable to care for my children.
    ';
  }
*/
  
  if (isset($texOrderData['guardian'][2]['fullname']) && $texOrderData['guardian'][2]['fullname'] > '') {
    $texdoc .= '
    If ' . $texOrderData['guardian'][1]['fullname'] . ' is unwilling or unable to act as guardian of my minor children,
    then I appoint ' . $texOrderData['guardian'][2]['fullname'] . ' of ' . $texOrderData['guardian'][2]['onelineaddress'] . ' as the guardian of my
    minor children.
    ';
  }
}
    
if (isset($texOrderData['trustForMinors']) && $texOrderData['trustForMinors'] == 'Yes') {
  $texdoc .= '
    \item
    If a beneficiary mentioned in this my will, is under the age of ' . $texOrderData['ageOfInheritance'] . ' years
    at the time of my death, then the gift or share of my estate that they will receive according
    to this my will, will be held in trust for them by My Trustee for the purpose of support,
    welfare and education until the beneficiary reaches the age of ' . $texOrderData['ageOfInheritance'] . ' years.

  ';
  if (isset($texOrderData['trustForMinorsConditions']) && $texOrderData['trustForMinorsConditions'] > '') {
    $texdoc .= $texOrderData['trustForMinorsConditions'];  
  }
}

if ($texOrderData['petCount'] > 0) {
  if ($texOrderData['petCount'] == 1) {

    if ($texOrderData['pet'][1]['carerrecipient'] == 'Other') {
      $texdoc .= '
      \item
      I wish that my ' . $texOrderData['pet'][1]['type'] . ', ' . $texOrderData['pet'][1]['name'] . ', be cared for as follows. \\\\
      ' . $texOrderData['pet'][1]['caredetails'];
    } else {
      $texdoc .= '
      \item
      It is my wish that my ' . $texOrderData['pet'][1]['type'] . ', ' . $texOrderData['pet'][1]['name'] . ', be cared for by ' . $texOrderData['pet'][1]['carerrelationship'] . ' ' . $texOrderData['pet'][1]['carerfullname'] . '.';
    }

    if ($texOrderData['pet'][1]['amount'] > '') {
      $texdoc .= '
      I direct my Trustee to give a cash gift of ' . $texOrderData['pet'][1]['amount'] . ' to '  . $texOrderData['pet'][1]['carerrelationship'] . ' ' . $texOrderData['pet'][1]['carerfullname'] . ' for the purposes of providing care to ' . $texOrderData['pet'][1]['name'] . '.';
    }
    $texdoc .= '
    ';
  } else {
    $texdoc .= '
      \item
      I direct My Trustee to make arrangements for my pets as follows:
      \begin{enumerate}[label=\roman*.]
    ';
    
    foreach($texOrderData['pet'] as $pet) {
      
      if ($pet['carerrecipient'] == 'Other') {
        $texdoc .= '
        \item
        I wish that my ' . $pet['type'] . ', ' . $pet['name'] . ', be cared for as follows. \\\\
        ' . $pet['caredetails'];
      } else {
        $texdoc .= '
        \item
        It is my wish that my ' . $pet['type'] . ', ' . $pet['name'] . ', be cared for by ' . $pet['carerrelationship'] . ' ' . $pet['carerfullname'] . '.';
      }
      
      
      if (isset($pet['amount'])) {
        $texdoc .= '
        I direct my Trustee to give a cash gift of ' . $pet['amount'] . ' to '  . $pet['carerrelationship'] . ' ' . $pet['carerfullname'] . ' for the purposes of providing care to ' . $pet['name'] . '.';
      }
      $texdoc .= '
      ';
    }
    $texdoc .= '
      \end{enumerate}
    ';
  }
}

    
if ($texOrderData['legacyCount'] > 0) {
  $texdoc .= '
    \item
    I direct My Trustee to give the following cash gifts (legacies) to my beneficiaries:
    \begin{enumerate}[label=\roman*.]
  ';
  
  foreach($texOrderData['legacy'] as $legacy) {
    $texdoc .= '
      \item
      ' . $legacy['amount'] . ' to ' . $legacy['relationship'] . ' ' . $legacy['fullname'] . '.
      ' . $legacy['condition'] . '
      ';
  }
  $texdoc .= '
    \end{enumerate}
  ';
}

    
if ($texOrderData['bequestCount'] > 0) {
  $texdoc .= '
    \item
    I direct My Trustee to give the following gifts of personal property (bequests) to my beneficiaries:
    \begin{enumerate}[label=\roman*.]
  ';
  
  foreach($texOrderData['bequest'] as $bequest) {
    $texdoc .= '
      \item
      I give my ' . $bequest['property'] . ' to ' . $bequest['relationship'] . ' ' . $bequest['fullname'] . '.
      ' . $bequest['condition'] . $bequest['altrecipientphrase'] . '
      ';
  }
  $texdoc .= '
    \end{enumerate}
  ';
}

    
if ($texOrderData['deviseCount'] > 0) {
  $texdoc .= '
    \item
    I direct My Trustee to give the following gifts of real estate (devises) to my beneficiaries:
    \begin{enumerate}[label=\roman*.]
  ';
  
  foreach($texOrderData['devise'] as $devise) {
    $texdoc .= '
      \item
      I give ' . $devise['property'] . ' to ' . $devise['relationship'] . ' ' . $devise['fullname'] . '.
      ' . $devise['condition'] . $devise['altrecipientphrase'] . '
      ';
  }
  $texdoc .= '
    \end{enumerate}
  ';
}

    
if ($texOrderData['estateCount'] > 0) {
  $texdoc .= '
    \item
    I direct My Trustee to pay any outstanding funeral, probate and testamentary expenses, taxes and duties,
    and to pay all my just debts from the remainder of my estate (the residuary). I direct My Trustee to pay
    the balance of my residuary, after the repayment of the aforementioned expenses, taxes, duties and debts,
    to my beneficiaries as follows:
    \begin{enumerate}[label=\roman*.]
  ';
  
  foreach($texOrderData['estate'] as $estate) {
    $texdoc .= '
      \item
      I give a share of ' . $estate['share'] . ' of my residuary to ' . $estate['relationship'] . ' ' . $estate['fullname'] . '.
      ' . $estate['condition'] . $estate['altrecipientphrase'] . '
      ';
  }
  $texdoc .= '
    \end{enumerate}
  ';
  
  if (isset($texOrderData['wipeoutClause']) && $texOrderData['wipeoutClause'] > '') {
    $texdoc .= '
      \item
      ' . $texOrderData['wipeoutClause'];
  }
} else {
  $texdoc .= '
    \item
    {\itshape DISTRIBUTION OF ESTATE - YOU HAVE NOT YET MADE ANY DISTRIBUTION OF YOUR ESTATE - PLEASE GO TO THE "DISTRIBUTION OF ESTATE" SECTION AND ALLOCATE YOUR ESTATE}
  ';
}

    
if ($texOrderData['requirementCount'] > 0) {
  $texdoc .= '
    \item
    I wish to have the following personal requirements carried out after my death:
    \begin{enumerate}[label=\roman*.]
  ';
  
  foreach($texOrderData['requirement'] as $requirement) {
    $texdoc .= '
      \item
      ' . $requirement['text'] . '
      ';
  }
  $texdoc .= '
    \end{enumerate}
  ';
}


if (isset($texOrderData['finalClause']) && $texOrderData['finalClause'] > '') {
	$texdoc .= '
		\item
		' . $texOrderData['finalClause'];
}

		
$texdoc .= '    
    \item
    This, my will, is governed by and to be interpreted in accordance with, the applicable laws
    of ' . $texOrderData['person']['fullstate'] . ' (which is the place of my residing at the time of signing this my will).
    
  \end{enumerate}
';

$texdoc .= '  
  %% Signature page
  \newpage
  \newgeometry{hmargin=3cm,vmargin=2.5cm}
  \renewcommand{\headrulewidth}{0pt}
  \renewcommand{\footrulewidth}{0.2mm}
  \lfoot{{\small \itshape Last Will and Testament of ' . $texOrderData['person']['fullname'] . '}}
  \rfoot{{\small \itshape Page \thepage~of \pageref{LastPage}}}

  \begin{center}
    {\itshape Dated this \rule{2cm}{0.2mm} day of \rule{3cm}{0.2mm} , \rule{1.5cm}{0.2mm} \\\\[2cm]}
  \end{center}

  \vspace{1.5cm}

  \begin{multicols}{2}
    \raggedright {\bfseries Signed by ' . $texOrderData['person']['fullname'] . '\\\\  {\small \itshape(the testator)}}:
    
    \columnbreak
    
    \rule{7cm}{0.2mm}\\\\  {\small \itshape (\ding{45}) Signature of testator}
  \end{multicols}
  
  \vspace{1.5cm}

  {\bfseries Attestation by witnesses:}

  Signed by the above testator in the presence of both of us the Witnesses being present
  at the same time and signed by each of us in the presence of each other and the testator.
  
  \vspace{2cm}

  \begin{multicols}{2}
    
    \hspace{1.5cm} {\bfseries Witness 1}  \\\\[2cm]
    \rule{7cm}{0.2mm}\\\\  {\small \itshape (\ding{45}) Signature of Witness 1} \\\\[1cm]
    \rule{7cm}{0.2mm}\\\\  {\small \itshape Print Name of Witness 1} \\\\[1cm]
    \rule{7cm}{0.2mm}\\\\[0.5cm]
    \rule{7cm}{0.2mm}\\\\[0.5cm]
    \rule{7cm}{0.2mm}\\\\[0.5cm]
    \rule{7cm}{0.2mm}\\\\ {\small \itshape Print Address of Witness 1}

    \columnbreak
    
    \hspace{1.5cm} {\bfseries Witness 2}  \\\\[2cm]
    \rule{7cm}{0.2mm}\\\\  {\small \itshape (\ding{45}) Signature of Witness 2} \\\\[1cm]
    \rule{7cm}{0.2mm}\\\\  {\small \itshape Print Name of Witness 2} \\\\[1cm]
    \rule{7cm}{0.2mm}\\\\[0.5cm]
    \rule{7cm}{0.2mm}\\\\[0.5cm]
    \rule{7cm}{0.2mm}\\\\[0.5cm]
    \rule{7cm}{0.2mm}\\\\ {\small \itshape Print Address of Witness 2}
  \end{multicols}

\end{document}

';
?>