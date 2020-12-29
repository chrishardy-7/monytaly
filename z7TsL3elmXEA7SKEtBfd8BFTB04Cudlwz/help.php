<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$nameOfThisPage = "Test";

include_once("./".$sdir."head.php");
include_once("./".$sdir."menu.php");


?>
<div style=" font-size:0.8vw;  padding-left:5vw; margin-top:1.5vw; padding-right:1.5vw; width:50vw; height:42vw; overflow-y: scroll;">

	<p style="margin-bottom: 0cm; font-size:1vw;"><b style="font-weight:bold;">Help</b></p>
	<p style="margin-bottom: 0cm"><br>

	<p style="margin-bottom: 0cm"><b style="font-weight:bold;">monytaly.uk</b> is a Web based
      application for displaying financial transactions the details of
      which are held in an online database. <font color="#0000cc">On
        first
        use you will be prompted to change your password.</font>
      Passwords
      must have at least 8 characters and must have at least:</p>
    <p style="margin-bottom: 0cm">one upper case letter</p>
    <p style="margin-bottom: 0cm">one lower case letter</p>
    <p style="margin-bottom: 0cm">one non-alphanumeric character # { $
      etc.</p>
    <p style="margin-bottom: 0cm">one number (0 - 9).</p>
    <p style="margin-bottom: 0cm">A widescreen HD screen (1080 x 1920 or better) gives optimum results.</p>
    <p style="margin-bottom: 0cm"><br>
    </p>
    <p style="margin-bottom: 0cm">Depending on individual user settings
      the system opens in either editing or non-editing mode.
      Users may also have limited views as required, e.g. only
      Furniture Project transactions.</p>
    <p style="margin-bottom: 0cm"><br>
    </p>
    <p style="margin-bottom: 0cm"><br>
    </p>

    <p style="margin-bottom: 0cm; font-weight:bold;"><b>1) Shortcut Buttons</b></p>
    <p style="margin-bottom: 0cm"> 
    <ul>
    	<li>
    	<p> Several shortcut buttons are provided at the top of the page:</p>
    	<li>
    	<p> Nov Reclaim: the expanded reclaim for the most recent month.</p>
      	</li>
      	</li>
      	<li>
    	<p> Records: the main spreadsheet that shows transactions for a selected date range.</p>
      	</li>
      	<li>
    	<p> Restricted 2020-21: the pivot table of the restricted budget allocations for the whole financial year.</p>
      	</li>
      	<li>
    	<p> Unrestricted 2020-21: the pivot table of the unrestricted budget allocations for the whole financial year.</p>
      	</li>     	
  	</ul>
    <p style="margin-bottom: 0cm"><br>
    </p>


    <p style="margin-bottom: 0cm; font-weight:bold;"><b>2) Display selective views of transactions with balances</b></p>
    <p style="margin-bottom: 0cm"> 
    <ul>
    	<li>
    	<p>Select required date range in left hand calander panel to display transactions:</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Choose the <b>year</b> with up/down buttons.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm"><font color="#0000cc">Select the <b>whole
              financial year</b> by clicking the year button (i.e.
            2021).</font></p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Select a <b>single month</b> by
          clicking required month button.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Select a <b>range of months</b>
          by clicking the start month and then clicking the end month
          while holding 'shift' down.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Select all dates since records
          began by clicking 'All' button.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Then - <b>s</b><b>croll</b>
          through the displayed list to any transaction in the selected
          date range.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Select a transaction row. The
          associated <b>document is displayed</b> and the row
          highlighted along with all other rows that use the same
          document as evidence.</p>
      </li>
    </ul>
    <p style="margin-bottom: 0cm"><br>
    </p>


    <p style="margin-bottom: 0cm; font-weight:bold;"> 3) Display withdrawn/paidin/balance:</p>
    <ul>
      <li>
        <p style="margin-bottom: 0cm">Click any cell to display the <b>sum
            of withdrawn amounts</b> and the sum of <b>paidin amounts</b>
          for matching fields. e.g. if 'General' is clicked in the
          'Accounts' column withdrawn and paidin values for the General
          Account for the displayed date range will be summed and
          displayed to the right of the 'Filtered' label near the bottom
          of the screen.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">A <b>balance</b> is shown to the
          right of the withdrawn and paidin sums which is the difference
          between them. </p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">This works with every column
          except Date, Withdrawn, Paidin, Reconciled and Family.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">A <b>Reconciled</b> section is
          also shown at the bottom of the screen - this accounts only
          for transactions that appear on the bank statement for the
          given month - assuming the reconciled column is up-to-date.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">If General Account is selected,
          the sums for <b>Reconciled</b> should mirror the values in
          the <b>bank statements</b><span style="font-weight: normal">
          </span><span style="font-weight: normal">PROVIDED</span><span
            style="font-weight: normal"> </span>the range of selected
          months is set to <b>start with </b><b>Apr</b> (first month
          of the financial year) so the <b>balance forward amounts</b>
          from the previous year will be included as transactions. This
          ensures that the displayed balances reflect the <b>true
            financial status</b> for the year up to the selected <b>end
            month</b>.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">For checking documents one at a
          time for integrity a <b>Doc Totals</b> section at the bottom
          right of the screen shows the recorded transaction sums for
          the selected document only. This shows the sum for all
          transaction lines using that document, even if they are not
          displayed because they are not part of the currently selected
          date range. The total number of transaction lines for the
          selected document are also shown, above which is the total
          number of lines for all transactoins in the selected date
          range.</p>
      </li>
    </ul>
    <p style="margin-left: 1.2cm; margin-bottom: 0cm"><br>
    </p>


    <p style="margin-bottom: 0cm; font-weight:bold;"> 4) Filter the displayed transactions:</p>
    <ul>
      <li>
        <p style="margin-bottom: 0cm">While holding the Control key down
          click a field in the list of transactions - The display will
          refresh to show only <b>transactions matching the selected
            field</b>, e.g. if Church Cash in the Account column is
          clicked only those transactions containing Church Cash will be
          displayed and the account column will be highlighted in
          yellow. </p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">OR - <font color="#0000cc">to
            display all bank statements hold down the </font><font
            color="#0000cc">C</font><font color="#0000cc">ontrol key and
          </font><font color="#0000cc">find and </font><font
            color="#0000cc">click 'Bank Statement' in the Doc Type </font><font
            color="#0000cc">c</font><font color="#0000cc">olumn.</font></p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">The filter can be removed by
          Control clicking the same entry again.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">The filtering action can be
          applied successively to as many columns as desired to narrow
          the range of transactions displayed, e.g. if a persons name is
          chosen in the Pers-Org column, along with Gift Aid Offering in
          the Trans Cat column, that person's gift aid contributions for
          the selected month range (which could be the whole year if
          desired) will be displayed. If the person's name is then
          clicked, their total <b>Gift Aid contribution for the whole
            year</b> will be displayed in the 'Filtered' section at the
          bottom.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Similsarly by holding down the
          Control and Shift key down together and clicking a field in
          the list of transactions - The display will refresh with <b>transactions
            matching the selected field</b><span style="font-weight:
            normal"> </span><span style="font-weight: normal">excluded</span><span
            style="font-weight: normal">.</span></p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">These two filter action can be
          combined and work for all columns except Date, Reconciled and
          Family. Filters are carried between different month selections
          but are canceled if the Records button is selected again from
          the main menu - this is the only way to remove the 'exlude'
          filter action.</p>
      </li>
    </ul>
    <p style="margin-bottom: 0cm"><br>
    </p>


    <p style="margin-bottom: 0cm; font-weight:bold;"> 5) Reclaims:</p>
    <ul>
      <li>
        <p style="margin-bottom: 0cm">Reclaim details are <b>normally
            hidden</b><span style="font-weight: normal"> </span><span
            style="font-weight: normal">but are represented by the
            reclaim amount in a transac</span><span style="font-weight:
            normal">t</span><span style="font-weight: normal">ion row
            ending in OOO xxxx at the right.</span></p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Clicking a OOO num item in the
          family column will <b>expand that reclaim</b> and hide
          everything else. Any filters that have been applied will be
          removed for the reclaim view but re-imposed once the
          reclaim/family view is closed.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Clicking the Include Expanded
          Reclaims button at the bottom will expand all reclaims
          included in the current date range and <b>integrate them with
            all the other transactions</b>. This is useful for grouping
          budgets to check spend for a whole year covering all
          transactions.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Clicking any number in the family
          column of an expanded reclaim will collapse it and display the
          normal transaction view.</p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Change of month selection will
          cancel an expanded reclaim but not integrated ones.</p>
      </li>
    </ul>
    <p style="margin-bottom: 0cm"><br>
    </p>


    <p style="margin-bottom: 0cm; font-weight:bold;"><b> 6) Pivot Table</b></p>
    <ul>
      <li>
        <p style="margin-bottom: 0cm">Clicking the Pivot button and the
          Show Families button in the bottom panel will display a budget
          'Pivot Table' showing overall Budgets brought forward,
          receipts, payments, surplus and carried forward for the
          current display of transations.</p>
      		</li>
          <li>
          <p> The various payment categories
          are shown down the left hand column and figures for the budget
          allocations are shown in the body of the table.</p> 
      	</li>
          <li>
          <p>Clicking more
          or less any figure or category will give a normal spreadsheet
          with appropriate filters applied to allow inspection of all
          the transactions with supporting documents for that budget
          allocation.</p>
          </li> 
          <li>
          <p>Similarly, clicking budget names or figures in the
          heading area can display details of budget income.</p>
      </li>
    </ul>
    <p style="margin-bottom: 0cm"><br>
    </p>


    <p style="margin-bottom: 0cm; font-weight:bold;"><b> 7) Download as a spreadsheet (csv file)</b></p>
    <ul>
      <li>
        <p style="margin-bottom: 0cm">Click the download button to
          download the current screen view as a spreadsheet csv file
          that can be opened in LibreOffice or MS Office. </p>
      </li>
      <li>
        <p style="margin-bottom: 0cm">Because csv files contain no
          formatting the spreadsheet will need to be formatted as
          desired - e.g. set values to 2 decimal places, adjust column
          widths, bold headings and justification etc. - and, if desired, saved in
          Excel or LibraOffice format.</p>
      </li>
    </ul>
    <p style="margin-bottom: 0cm"><br>
    </p>
   <p>Please email any bug descriptions to: <a href="mailto:chris@thehardys.net">chris@thehardys.net</a></p>
    <p style="margin-bottom: 0cm"><br>
    </p>





 <u hidden>
    <p style="margin-bottom: 0cm; font-weight:bold;"><b>?) Upload document scans</b></p>
    <p style="margin-bottom: 0cm"><i>Topic still to be written</i></p>
    <p style="margin-bottom: 0cm"><br>
    </p>


    <p style="margin-bottom: 0cm; font-weight:bold;"><b> ?) Create one or more transaction
        lines for each document</b></p>
    <p style="margin-bottom: 0cm"><i>Topic still to be written</i></p>
    <p style="margin-bottom: 0cm"><br>
    </p>
    <p style="margin-bottom: 0cm"><br>
    </p>

    <p style="margin-bottom: 0cm; font-weight:bold;"> ?) Group an item: (NOT FUNCTIONING AT
      MOMENT)</p>
    <ul>
      <li>
        <p style="margin-bottom: 0cm"><font color="#b2b2b2">Click the
            heading of a column to display <b>overall sums and balances
              for each item</b> in that column as a list of groups. e.g.
            clicking the Budget column heading will show each budget
            once with the Withdrawn, Paidin and Balance figures for each
            budget. </font> </p>
      </li>
      <li>
        <p style="margin-bottom: 0cm"><font color="#b2b2b2">If a Control
            click is done on a specific budget the group function is
            cancelled and that budget is used as a filter term so
            details of each transaction can be listed. </font> </p>
      </li>
      <li>
        <p style="margin-bottom: 0cm"><font color="#b2b2b2">Click the
            heading again switches group view <b>back to normal view</b>.</font></p>
      </li>
      <li>
        <p style="margin-bottom: 0cm"><font color="#b2b2b2">Doesn't work
            with Date column.</font></p>
      </li>
    </ul>
    <p style="margin-bottom: 0cm"><br>
    </p>
</u>

</div>

<?php


include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>
