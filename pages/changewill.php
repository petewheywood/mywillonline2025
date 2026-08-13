<?php

# check the page is not accessed directly and redirect if so
if (!isset($page)) {
  header("Location: /");
  exit;
}

$title = "When Should I Change My Will? | " . SITENAME;
$desc = "Update your will after major life events — marriage, divorce, birth of children, or changes to finances. Free lifetime updates at My Will Online.";
$keywords = "changing a will in australia, when do I need to change my will, reasons for changing a will, how to change a will in australia";

require("pageincludes/header.php");
?>
      <section class="container narrow">
        <h1>When do I need to change my Will?</h1>
        <p>
          It is really important to make changes to your Will when major life events happen. Marriages, divorces, separations, and the birth of children are all times
          when you should re-write your will.
          All these events will have some effect on your wishes for the breakup of your estate and on the validity of your current Will.
        </p>
        
        <h2>Marriage/Civil Partnership</h2>
        <p>
          When you get married, any will that you currently have is revoked.
          An exception to this is if your will clearly states that a marriage is imminent and
          contains unambiguous instructions that you intend that the will is to remain valid after the marriage happens.        	
        </p>

        <h2>Divorce/Separation</h2>
        <p>
        	If you do not update your will when you separate from your partner and then die, your partner will probably inherit any property you left to them in your current, unchanged will. 
        	Also, if you have made your (separated) partner your executor, they can fulfil that role regardless of whether you still wanted them to or not.
        </p>
        <p>
          Divorce affects your will differently in each state and territory. In some states, divorce will automatically render your will invalid.
          In others, a divorce will revoke your former spouse as your executor or any gift left to them.
          For example, in NSW,
          a divorce will stop your former partner being an executor and will stop them receiving a gift specified in the will. However, they may still
          act as trustee for your will. 
        </p>
        <p>
        	It's better to write a new will than to let the courts decide whether your ex-partner receives any benefit or authority to act as an executor for your Will.
        </p>
        
        <h2>Does being in a de facto relationship affect my will?</h2>
        <p>
        	Living with a de facto partner does not have the same effect on a will as marriage does.
        	However, a de facto relationship does confer some property rights on a partner.
        	You should make sure that your will is not inconsistent with these implicit de facto rights.
        </p>
        <p>
        	If you separate from your de facto partner and then die before a property settlement is reached, your de facto partner will be the one who legally retains
        	your property. If you don't want this to happen, you need to write a new will.
        </p>
        
        <h2>Birth of a child</h2>
        <p>
        	If you have a child after writing your will, they will not automatically become a beneficiary. They would certainly have rights to your property under
        	state government rules, but to make sure that they are included in the distribution of your estate as you wish, you should re-write your will.
        </p>

        <h2>Significant change in your financial circumstances</h2>
        <p>
        	If you have a significant financial windfall, then it is probably a good idea to update your will. You may like to distribute your assets in a 
        	different way than you had previously decided. You may even want to add additional beneficiaries to your will.
        </p>
        
        <h2>How to change your will</h2>
        <p>
        	To change your Will, you need to create what is called a <a href="https://en.wikipedia.org/wiki/Codicil_(will)" title="A codicil alters a will" target="t2">codicil</a>
        	to the current will or you need to create an entirely new will which revokes and replaces	the preceding will.
        </p>
        <p>
        	If you wish to make changes, it is better to make an entirely new Will. Creating a codicil can be just as much work as writing a completely new will, and can add complexity to the process required
        	after you die.
        </p>
        <br>
      </section>
      <div class="text-center">
        <br>
        <a href="<?php echo isset($_SESSION['loggedin']) ? 'mydocs.html' : 'start.html';?>" class="btn btn-success"><i class="glyphicon glyphicon-pencil"></i> Make Your Will</a><br>
        <br>
      </div>
      

<?php
  require("pageincludes/footer.php");
?>