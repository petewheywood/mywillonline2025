<?php
$fullname = isset($texOrderData['testatorFullName']) ? 'for ' . $texOrderData['testatorFullName'] : '';
$texdoc = 
'\documentclass[11pt,a4paper]{article}

\usepackage{times}  
\usepackage[utf8]{inputenc}
\usepackage{enumerate}
\usepackage[margin=2cm]{geometry} 

\newcommand{\HRule}{\rule{\linewidth}{0.2mm}}

\title{Instructions}
\author{My Will Online}


\begin{document}
%\maketitle
\thispagestyle{empty}
\begin{center}
\section*{Last Will and Testament Instructions\\\\' . $fullname . '}
\end{center}
\noindent \HRule \\\\
\begin{enumerate}
	\item Print your Will, for signing.
	\item Sign each page in the presence of two witnesses.
	\item Ensure that each witness watches you sign the will, and that they watch each other sign.
	\item On the last page of the Will there is a main signature page. You and your witnesses must sign and date this page and the witnesses should write their names and addresses in the space provided.
	\item Your witnesses should not be a beneficiary or the spouse of a beneficiary named in your will.
	\item Once the original will is signed and dated, it becomes a valid Last Will and Testament.
	\item No pins or paper clips should be attached to the original will.
	\item Make copies of this original will as required.
	\item It is very important that the original will be stored in in a safe place so that it can be located by your executors when it is needed. Some suggestions for safe storage:
	\begin{enumerate}[i.]
		\item Lodge it with a bank in a safe custody envelope or packet.
		\item Lodge it with your solicitor.
		\item Lodge it with a Trustee company in your state.
	\end{enumerate}
	\item Inform your executors where the original copy of your will is stored.
	\item If your circumstances change substantially, then you will need to update your will, and sign and date the new will.
	\item Some personal circumstance changes will invalidate your current will and in these cases you must update your will to reflect your new circumstances. 
	\begin{enumerate}[i.]
		\item Marriage/Remarriage - when a person marries or remarries, any existing will is invalidated and the person becomes intestate unless a new will is made.
		\item Divorce will invalidate a will in some states of Australia. Regardless of which state you reside in, it is strongly recommended that should you become divorced, you should make a new will.
		\item If you are in a de facto relationship, you and your partner should have separate wills. If the relationship ends, then each partner should make a new will.
		\item You should consider making a new will if any of the persons mentioned within your will (executors, guardians, beneficiaries) change their names or die.
		\item And of course, you should make a new will when you change your mind about which beneficiaries will receive what from your estate.
	\end{enumerate}
\end{enumerate}';

$texdoc .=
'~\\\\
\begin{center}
{\itshape This will was produced by ' . SITENAME . ', ' . COUNTRY . ' (' . SITEURL . ') }
\end{center}
\vfill
\noindent \HRule
\end{document}';