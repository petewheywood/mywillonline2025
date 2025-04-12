<?php
# check tht page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "Questions About Wills in Australia - " . SITENAME;
$desc = "You can find the answers to many common questions about Wills in Australia on this page. ";
$keywords = "questions about Wills";

$noJS = !isset($_SESSION['javascriptEnabled']);

require("pageincludes/header.php");

?>
      <section class="container narrow">
        <h1>Frequently Asked Questions</h1>
        
        <div id="accordion" class="panel-group">
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q1">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                What is a Will?
              </h4>
            </div>
            <div id="q1" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					A will is a legal document that clearly sets out how you wish your assets to be distributed when you die. Also, if you have minor children, it
        					nominates a guardian (or guardians) who will care for them after your death.
        				</p>
              </div>
            </div>
          </div>

          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q2">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                How long will it take me to make my will?
              </h4>
            </div>
            <div id="q2" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
                <p>
        					It could take you as little as 5 minutes if you have a partner and wish to leave 100% of your estate to them.
        					If you have more beneficiaries (children, other family members, friends, and so on) and if you have a number of
        					gifts to list, and lots of information to include it will take longer.
        				</p>
        				<p>
        					In general, however, most <?php echo $_SESSION['sitename'];?> customers take around 30-40 minutes to complete entering
        					their information and to download their completed will document package.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q3">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                Do I need a Will?
              </h4>
            </div>
            <div id="q3" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					Many people wonder whether they need a will. So, maybe the best way to determine the answer is to let
        					you know what can happen if you don't have a will when you die.
        				</p>
        				<p>
        					If you die without a will, your estate (all that you own) is divided up according to standard rules
        					(called &ldquo;intestacy rules&rdquo;).
        					It would be far better for you to decide who gets what from your estate rather than have it divided
        					according to a government imposed legal formula.
        				</p>
        				<p>
        					If your only living relatives are no closer than cousins, your entire estate will pass to the government.
        				</p>
        				<p>
        					Making a Will is the only way you can ensure your assets will be distributed according to your wishes when you die. 
        					Studies have shown that at least 45% of Australians do not have a valid Will.
        				</p>
        				<p>
        					In addition to not having your estate distributed according to your wishes, your family will incur additional 
        					cost and hardship if you die without a will.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q4">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                What is required for my will to be legally valid?
              </h4>
            </div>
            <div id="q4" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
       					<p>
        					Your will is legally valid if the following criteria are complied with:
        				</p>
        				<ol>
        					<li>You must be 18 years or older unless married.</li>
        					<li>Your will must be in writing. That is, it must be either handwritten, typed or printed.</li>
        					<li>It must be signed by the person making the will in the presence of at least 2 witnesses who must also sign the will. The witnesses should not be beneficiaries of the will.</li>
        					<li>
        						The will maker must have what is known as &ldquo;testamentary capacity&rdquo;. This means that they must:
        						<ul>
        							<li>understand the nature of the act of making a will (in other words, understand what the will is for)</li>
        							<li>understand the extent of their estate (know what assets they have)</li>
        							<li>understand who has a reasonable claim upon their estate (that is, which family members and others it is appropriate to leave something to in their will)</li>
        							<li>not suffer from what the court cases call a &ldquo;disease of the mind&rdquo;. In other words, they are suffering from something that prevents them from 
        							making a rational decision. For example: dementia, mental illness, or substance induced psychosis.</li>
        						</ul>
        					</li>
        					<li>
        						There must be no undue influence, coercion or duress on the person.
        						Many elderly people are pressured by others including relatives, carers and neighbours.
        						Undue influence is pressure that falls short of duress and usually exists where there is
        						some sort of relationship between the parties. Duress or coercion is based on a threat
        						to cause harm and is generally thought of as considerably more serious than undue influence.
        					</li>
        				</ol>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q5">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                What events will make my will invalid?
              </h4>
            </div>
            <div id="q5" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					There are events that happen in life that will make a previously valid will invalid. They include:
        				</p>
        				<ul>
        					<li>getting married</li>
        					<li>getting divorced</li>
        				</ul>
        				<p>
        					Should either of these events happen. You need to make another will.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q6">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                When should I update my will?
              </h4>
            </div>
            <div id="q6" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					You should revisit your will every 5-7 years as a guideline and update your will if:
        				</p>
        				<ul>
        					<li>your financial circumstances change</li>
        					<li>your family circumstances change, for example, if you marry, start a new relationship, divorce, separate, or have children or grandchildren</li>
        					<li>a beneficiary under your current will dies</li>
        					<li>an executor or trustee appointed under your current will dies or becomes unsuitable to act due to age or ill-health</li>
        					<li>you sell or give away assets that are specifically mentioned in your will</li>
        					<li>you buy or inherit significant assets</li>
        					<li>you begin to hold assets that your will cannot deal with, such as in superannuation or a trust</li>
        				</ul>                    
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q7">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                What is the executor of a will?
              </h4>
            </div>
            <div id="q7" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					An executor (or executors) is the person or organisation the will maker has selected to administer the 
        					estate, within the terms of the will, after the death of the will maker. 
        				</p>
        				<p>
        					The executor should be someone who understands the financial, legal and taxation obligations of the will and the estate.
        					Should any disputes arise over the will, an independent executor is often a wise choice.
        				</p>
                
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q8">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                How often can I change my will?
              </h4>
            </div>
            <div id="q8" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					If your circumstances change considerably, then you should re-write
        					your will.
        				</p>
        				<p>
        					If you get married or divorced, then your current will is invalidated and you must re-write your will. 
        				</p>
        				<p>
        					Your new will supersedes anything you have written in an earlier will.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q9a">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                How do I provide for my pets in my Will?
              </h4>
            </div>
            <div id="q9a" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
         				<p>
        					Under the law, a pet is considered to be your property, and as such they cannot be a beneficiary of any gifts or a share of your estate.
        					However, you can make provision for them in your will by allocating a carer, which may be an individual or an organisation such as an animal charity.
        				</p>
        				<p>
          				You would typically provide a legacy (cash gift) or share of your estate to the carer so they can provide ongoing care to the pet for the rest of its life.
          				Animal charities that provide pet legacy programmes would typically use the gift to assist with finding a new home for your pet.
        				</p>
        				<p>
                  It's advisable to speak with an individual who you'd like to care for your pet before hand so that you can be comfortable that your pet will be cared for as you wish.
        				</p>
        				<p>
          				<?php echo SITENAME;?> makes it easy to care for your pet by including a section in our <?php echo $_SESSION['premiumwill'];?> specifically to provide for your pet after you're gone.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q9">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                What happens if I die without a will and I am in a de-facto relationship?
              </h4>
            </div>
            <div id="q9" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
         				<p>
        					Under the law, your de-facto partner is considered your legal spouse, and he or she may be entitled to a share
        					of your estate. But the onus is on them to prove that you were in fact in a de-facto relationship if there are any disputes.
        				</p>
        				<p>
        					To be certain that your de-facto partner receives their share of your estate, you need to create a will that details
        					how your estate should be divided and include them specifically by name.
        				</p>                   
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q10">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                What happens if I die without a will and I am in a de-facto same sex relationship?
              </h4>
            </div>
            <div id="q10" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					Under the law, your de-facto same sex partner is considered your legal spouse, and he or she may be entitled to a share
        					of your estate. But the onus is on them to prove that you were in fact in a de-facto relationship if there are any disputes.
        				</p>
        				<p>
        					To be certain that your de-facto same sex partner receives their share of your estate, you need to create a will that details
        					how your estate should be divided and include them specifically by name.
        				</p>                    
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q11">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                Can my will be contested?
              </h4>
            </div>
            <div id="q11" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					Yes. People who have been left out of a will, or feel that they should have received a larger share of the estate may bring
        					a claim against the estate, if they can establish that they are an eligible person.
        				</p>
        				<p>
        					People who are eligible persons include spouses, de facto spouses, former spouses, children, stepchildren, grandchildren
        					and other people who were members of the will makers household and dependant or partially dependant on the will maker
        					(e.g. live-in housekeepers and carers).
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q12">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                Is my superannuation included in my estate?
              </h4>
            </div>
            <div id="q12" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					In most cases, your superannuation is not included in the estate. But this differs by state. 
        					The superannuation company who holds your superannuation will decide how your benefit is paid out.
        				</p>
        				<p>
        					You should consider making a &ldquo;binding nomination&rdquo; with your super fund.
        					This will allow you to nominate beneficiaries of your choice that the trustee of the super fund must honour. You should contact
        					your super fund trustee to arrange this.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q13">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                Where should I keep my will?
              </h4>
            </div>
            <div id="q13" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					It is important that your will can be found after your death. Store the original of your will in a safe
        					place, and tell those close to you where it is stored. It is also a good idea to let your executors know where
        					your will is stored.							
        				</p>
        				<p>
        					You may make as many copies of your completed and signed original will as you like.
        				</p>
        				<p>
        					If the will cannot be located after your death, then you are essentially dying intestate (without a will).
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q14">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                If I wish to change my will, do I need to pay again?
              </h4>
            </div>
            <div id="q14" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					Premium Wills purchased at <?php echo $_SESSION['sitename'];?> can be edited and downloaded again as often as you require for life.
        					There are no further costs associated with changing and downloading again.							
        				</p>
        				<p>
          				Quick Wills, however, cannot be updated and re-downloaded free of charge. You would need to create and purchase a new Quick Will. 
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q15">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                What is an Enduring Power of Attorney?
              </h4>
            </div>
            <div id="q15" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
         				<p>
        					An &ldquo;enduring power of attorney&rdquo; is a legal agreement that
        					enables a person to appoint a trusted person (or persons) to make financial and/or property decisions on their behalf.
        				</p>
        				<p>
        					The benefit of an enduring power of attorney is that unlike an ordinary power of attorney,
        					it will continue to operate even if the donor loses full legal capacity.
        				</p>
        				<p>
        					An enduring power of attorney ceases to have effect when the person dies. From that point on, the person&apos;s will
        					dictates what happens to their estate.
        				</p>
        				<p>
        					An &ldquo;enduring power of attorney&rdquo; is different in each state and a distinct form is used to create one for each state.
        					In some states an enduring power of attorney covers only financial and property matters, and in others, it may also cover health matters.
        				</p>
        				<p>
        					If you select an Enduring Power of Attorney document along with your will, it will also include guidelines on how to complete
        					the document.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q16">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                Why should I have an Enduring Power of Attorney?
              </h4>
            </div>
            <div id="q16" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					You may not always be able to make decisions when you need to.
        					You may be too ill to make choices about your financial matters,
        					or you could suffer a disability that prevents you from communicating your wishes to others.
        				</p>
        				<p>
        					Having an &ldquo;enduring power of attorney&rdquo; allows your estate to be managed by someone you
        					trust if you are no longer able to manage it yourself.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q17">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                When does an Enduring Power of Attorney take effect?
              </h4>
            </div>
            <div id="q17" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					The &ldquo;enduring power of attorney&rdquo; will take effect whenever you decide. It could take effect immediately
        					or when some event occurs, such as you becoming incapable of managing your affairs. There are clauses in the
        					enduring power of attorney document that specify when it will become effective.
        				</p>
              </div>
            </div>
          </div>
          
          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q18">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                What is an Executor's Memorandum?
              </h4>
            </div>
            <div id="q18" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					An Executor's Memorandum is a document that accompanies your will which provides your executor with
        					information that will help them administer the wishes of your will. It typically includes: names and
        					contact details of beneficiaries and guardians, asset and liability lists and other information that
        					may be useful such as bank account numbers, life insurance policies, online asset information (such
        					as Facebook login details and passwords) and so on.
        				</p>
        				<p>
        					<?php echo $_SESSION['sitename'];?> includes an Executor's Memorandum with all wills.
        				</p>
              </div>
            </div>
          </div>              

          <div  class="panel panel-default">
            <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" href="#q19">
              <i class="glyphicon glyphicon-plus-sign pull-right"></i> 
              <h4 class="panel-title">
                I have a family trust. Can I make a will with <?php echo $_SESSION['sitename'];?>?
              </h4>
            </div>
            <div id="q19" class="panel-collapse collapse<?php echo $noJS ? ' in' : '';?>">
              <div class="panel-body">
        				<p>
        					If your circumstances are complex (such as having a family trust, or a company), we highly 
        					recommend that you get your Will drafted by a solicitor.
        				</p>
        				<p>
        					<?php echo $_SESSION['sitename'];?> works for the majority of people, but does not
        					attempt to cater for more complex arrangements that some individuals may have.
        				</p>
              </div>
            </div>
          </div>              
        </div>
        <br>&nbsp;<br>
      </section>
<?php
  require("pageincludes/footer.php");
?>