<?php
class Report{

    
    private $host="192.168.0.26"; //live sofia server
    private $user="saprog";
    private $pass = 'SQL@2012!';
    private $db="GLOBAL_SOFIADB";
    private $conn;

    private $connJeon;
    private $hostJeon="192.168.0.160"; //testserver
    private $port = "49406";
    private $userJeon="lms";
    private $passJeon="rvn123456@2020!";
    private $dbJeon="rvn_crm"; 



    public function __construct(){
        $this->connJeon=  new PDO("sqlsrv:server=".$this->hostJeon.",49406;Database=".$this->dbJeon, $this->userJeon, $this->passJeon);
        $this->conn=  new PDO("sqlsrv:server=".$this->host.";Database=".$this->db, $this->user, $this->pass);
 }

 public function reseed(){
   $sql = "delete from crm_co_borrower_co_maker where id > 0";
   
   $stmt = $this->conn->prepare($sql);
   $stmt->execute(array());

   $ssql = "delete from crm_character_reference where id > 0";
   $sstmt = $this->conn->prepare($ssql);
   $sstmt->execute(array());

   $sssql = "delete from crm_telemarketing where id > 0";
   $ssstmt = $this->conn->prepare($sssql);
   $ssstmt->execute(array());

   if($ssstmt){
    $ssssql = "delete from crm_loan_information where id > 0";
    $sssstmt = $this->conn->prepare($ssssql);
    $sssstmt->execute(array());
    $rsql = "DBCC CHECKIDENT (crm_loan_information, RESEED, 0);
             DBCC CHECKIDENT (crm_co_borrower_co_maker, RESEED, 0);
             DBCC CHECKIDENT (crm_character_reference, RESEED, 0);
             DBCC CHECKIDENT (crm_telemarketing, RESEED, 0);";
             $rstmt = $this->conn->prepare($rsql);
             $rstmt->execute(array());
    echo 'Data reseeded';
   }
 }
 public function Sofia(){
    $sql = "select dbo.format_date(dbo.get_renewal_date(lm.LOAN_PN_NUMBER)) as Ren_Date, /**Renewable Date**/

    bch.BRAN_NAME as 'Branch', /*Branch*/
    
    '' as 'Kiosk',
    
    ltrim(rtrim(isnull(b.BORR_LAST_NAME,''))) + ', ' + 
            ltrim(rtrim(isnull(b.BORR_FIRST_NAME,''))) + ' ' + 
            ltrim(rtrim(isnull(b.BORR_SUFFIX,''))) + ' ' + 
            ltrim(rtrim(isnull(b.BORR_MIDDLE_NAME,''))) as 'Borr_Name',  /*Borrowers Name*/
    
    
    lm.LOAN_PN_NUMBER as 'PN_Number', /*PN Number*/
    
    dbo.mobileno_parse(dbo.fix_tel(b.BORR_TELNO),1) as 'Tel_No1', /*Telephone Number 1*/
     '' as 'Tel_No2', /*Telephone Number 2*/	
     '' as 'Tel_No3', /*Telephone Number 2*/
     '' as 'Tel_No4', /*Telephone Number 2*/
     '' as 'Tel_No5', /*Telephone Number 2*/	
    
    dbo.mobileno_parse(dbo.fix_tel(b.BORR_MOBILENO),1) as 'Mob_No1', /*Mobile Number 1*/
    '' as 'Mob_No2', /*Mobile Number 2*/
    '' as 'Mob_No3', /*Mobile Number 2*/
    '' as 'Mob_No4', /*Mobile Number 2*/
    '' as 'Mob_No5', /*Mobile Number 2*/
     
    
    prod.PROD_NAME Prod_Type,/*Product Type*/
    
    CASE 
          WHEN lm.LOAN_APP_TYPE = '1' THEN 'New'
          ELSE 'Renewal'
    END as 'Loan_Class', /*Loan Class*/
    
    CASE 
          WHEN lm.LOAN_STATUS = '5' THEN 'Released'
          ELSE 'ReProcess'
    END as 'Loan_Status', /*Loan Status*/
    
    dbo.format_date(lm.LOAN_LAST_DUEDATE) as 'Maturity_Date', /*Maturity Date*/
    
    lm.LOAN_TERMS as 'Terms', /*Terms*/
    
    cast(((lm.LOAN_MONTHLYAMORT * lm.LOAN_TERMS) - dbo.get_total_payment(lm.LOAN_PN_NUMBER)) / NULLIF(lm.LOAN_MONTHLYAMORT,0) as int) as 'Remain', /*Remain*/
    
    (lm.LOAN_PNVALUE - dbo.get_total_payment(lm.LOAN_PN_NUMBER)) as 'Oustanding_Bal',	/*Outstanding Blance*/
    
    lm.LOAN_AMOUNT as 'TLoan_Amount', /*Total Loan Amount*/
    
    lm.LOAN_PNVALUE as 'PN_Value',	/*PN Value*/
    
    dbo.get_total_payment(lm.LOAN_PN_NUMBER) as 'Amort_Paid', /*Amortization Paid*/
    
    
    dbo.format_date(dbo.get_last_payment(lm.LOAN_PN_NUMBER)) as 'Last_Principal_Payment_Date', /*Last Principal Payment Date*/
    
    dbo.get_name_from_employee(la.LCOM_AGENT_CODE) as 'Direct_Agent', /*Loan Agent*/
    
    bch.BRAN_URL as 'Area', /*Area*/
    
    ltrim(rtrim(isnull(bw.WBOR_ADDRESS1,''))) + '/' + ltrim(rtrim(isnull(bw.WBOR_ADDRESS2,''))) as 'Bus_Address', /*Business Address*/
    
    dbo.format_date(lm.LOAN_RELEASED_DATE) as 'Released_Date', /*Released Date*/
    
    ltrim(rtrim(isnull(emp.EMPL_LASTNAME,''))) + ', ' + 
            ltrim(rtrim(isnull(emp.EMPL_FIRST_NAME,''))) + ' ' + 
            ltrim(rtrim(isnull(emp.EMPL_SUFFIX,''))) + ' ' + 
            ltrim(rtrim(isnull(emp.EMPL_MIDDLENAME,''))) as 'CRD_Name', /*CRD Name*/
    
       '' as 'Status',/*Status*/
      '' as 'Note',/*Note*/
      '' as 'Remarks', /*Remarks*/
      dbo.format_date('') as 'Date', /*Date*/
      '' as 'Last_Modified_By', /*Last Modified By*/
    
    dbo.get_borrowers_name(cm.LMKR_COMAKER_CODE) as 'Co_Borrower', /*Co Borrower Name*/
    dbo.fix_tel(dbo.get_borrower_contact(cm.LMKR_COMAKER_CODE)) as 'Co_Borrower_Contact', /*Co Borrower Contact No.*/
    
    '' as 'Co_Maker', /*Co Maker Name*/
    '' as 'Co_Maker_Contact', /*Co Maker Contact No.*/
    
    dbo.GetReferenceName(lm.LOAN_BORROWER_CODE) as 'Ref_Name', /*Character Referrence*/
    dbo.GetReferenceContact(lm.LOAN_BORROWER_CODE) as 'Ref_Contact' /*Character Referrence Contact No.*/
    
    from LM_LOAN lm
    left JOIN PR_BORROWERS b 

      ON lm.LOAN_BORROWER_CODE = b.BORR_CODE
    
   left JOIN PR_BRANCH bch 

      ON lm.LOAN_BR = bch.BRAN_CODE
    
   left JOIN PR_PRODUCT prod 
      
      ON prod.PROD_CODE = lm.LOAN_PRODUCT_CODE
    
   left join PR_BORROWERS_WORK bw 

      ON lm.LOAN_BORROWER_CODE = bw.WBOR_CODE
    
  left  join PR_EMPLOYEE emp 

      ON lm.LOAN_CRD_CODE = emp.EMPL_CODE -- CRD
    
 left join LM_LOAN_COMAKERS cm 

      ON lm.LOAN_PN_NUMBER = cm.LMKR_PN_NUMBER
      
   left join LM_LOAN_AGENTS la 
 
      ON lm.LOAN_PN_NUMBER= la.LCOM_PN_NUMBER

		where lm.LOAN_STATUS = '5' and lm.LOAN_APP_TYPE != 4 and lm.LOAN_APP_TYPE != 3
	
	        group by  
      lm.LOAN_PN_NUMBER, bch.BRAN_NAME, b.BORR_LAST_NAME, 
      b.BORR_FIRST_NAME, b.BORR_MIDDLE_NAME, b.BORR_SUFFIX, b.BORR_TELNO, b.BORR_MOBILENO, prod.PROD_NAME,
      lm.LOAN_APP_TYPE, lm.LOAN_STATUS, lm.LOAN_LAST_DUEDATE, lm.LOAN_TERMS, lm.LOAN_MONTHLYAMORT, lm.LOAN_PNVALUE,
      lm.LOAN_AMOUNT, la.LCOM_AGENT_CODE, bch.BRAN_URL, bw.WBOR_ADDRESS1, bw.WBOR_ADDRESS2, lm.LOAN_RELEASED_DATE,
      emp.EMPL_LASTNAME, emp.EMPL_FIRST_NAME, emp.EMPL_SUFFIX, emp.EMPL_MIDDLENAME, cm.LMKR_COMAKER_CODE, lm.LOAN_BORROWER_CODE";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(array());
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $i = 1;
      foreach($data as $k => $v){

  
          if($data){
            

            $diff = $v['PN_Value'] / 2;

            



            if($v['Prod_Type'] != 'Modified Business Loan-1' &&
            $v['Prod_Type'] != 'Modified Business Loan' &&
            $v['Prod_Type'] != 'Business Loan (NO PDC)' &&
            $v['Prod_Type'] != 'Business Loan - NWBL1' &&
            $v['Prod_Type'] != 'Business Loan - NWBL1 (no CA)' &&
            $v['Prod_Type'] != 'Business Loan - NWBL2' &&
            $v['Prod_Type'] != 'Business Loan w/ CA' &&
            $v['Prod_Type'] != 'Modified Business Loan - RCL' &&
            $v['Prod_Type'] != 'Micro-Business Loan' &&
            $v['Prod_Type'] != 'Motorcycle Loan' &&
            $v['Prod_Type'] != 'Motorcycle Loan - Personal Loan' &&
            $v['Prod_Type'] != 'SME - LINE' &&
            $v['Prod_Type'] != 'SME - LITE' &&
            $v['Prod_Type'] != 'Agricultural Loan' &&
            $v['Prod_Type'] != 'Agricultural Loan-Monthly' &&
            $v['Prod_Type'] != 'SME - CAPITAL' &&
            $v['Prod_Type'] != 'Allottee Loan' &&
            $v['Prod_Type'] != 'Micro Loan' &&
            $v['Prod_Type'] != 'Beneficiary Loan'
            ){





              // Car Loan
              // Car Loan (NOPDC)
              // Car Loan - Takeout
              // Car Loan - Takeout NO PDC
              // Car Loan Renewal Express
              // Car Loan Renewal Express (NO PDC)
              // Doctors Loan
              // Doctors Loan (NOPDC)



            if($v['Prod_Type'] == 'Doctors Loan' && $v['Prod_Type'] == 'Doctors Loan (NOPDC)'){


              $diff = $v['PN_Value'] / 2 / 5 * 4;


            }

            

            if($v['Amort_Paid'] >= $diff){






            

               $sstmt = $this->connJeon->prepare("INSERT INTO crm_loan_information(info_renewable_date,
               info_branch,
               info_kiosk,
               info_borrower,
               info_pn_number,
               info_telephone_number1,
               info_telephone_number2,
               info_telephone_number3,
               info_telephone_number4,
               info_telephone_number5,
               info_mobile_number1,
               info_mobile_number2,
               info_mobile_number3,
               info_mobile_number4,
               info_mobile_number5,
               info_product_type,
               info_loan_class,
               info_loan_status,
               info_maturity_date,
               info_term,
               info_remain,
               info_outstanding_balance,
               info_total_loan_amount,
               info_pn_value,
               info_amortization_paid,
               info_last_principal_payment_date,
               info_direct_agent,
               info_area,
               info_bussiness_address,
               info_released_date,
               info_crd_reviewed_by,
               cron_date
               )values(
               :info_renewable_date,
               :info_branch,
               :info_kiosk,
               :info_borrower,
               :info_pn_number,
               :info_telephone_number1,
               :info_telephone_number2,
               :info_telephone_number3,
               :info_telephone_number4,
               :info_telephone_number5,
               :info_mobile_number1,
               :info_mobile_number2,
               :info_mobile_number3,
               :info_mobile_number4,
               :info_mobile_number5,
               :info_product_type,
               :info_loan_class,
               :info_loan_status,
               :info_maturity_date,
               :info_term,
               :info_remain,
               :info_outstanding_balance,
               :info_loan_total_amount,
               :info_pn_value,
               :info_amortization_paid,
               :info_last_principal_payment_date,
               :info_direct_agent,
               :info_area,
               :info_bussiness_address,
               :info_released_date,
               :info_cd_reviewed_by,
               :cron_date)");    
      
             
              

          
               $sstmt->bindParam(':info_renewable_date',$info_renewable_date);
               $sstmt->bindParam(':info_branch',$info_branch);
               $sstmt->bindParam(':info_kiosk',$info_kiosk);
               $sstmt->bindParam(':info_borrower',$info_borrower);
               $sstmt->bindParam(':info_pn_number', $info_pn_number);
               $sstmt->bindParam(':info_telephone_number1', $info_telephone_number1);
               $sstmt->bindParam(':info_telephone_number2', $info_telephone_number2);
               $sstmt->bindParam(':info_telephone_number3', $info_telephone_number3);
               $sstmt->bindParam(':info_telephone_number4', $info_telephone_number4);
               $sstmt->bindParam(':info_telephone_number5', $info_telephone_number5);
               $sstmt->bindParam(':info_mobile_number1', $info_mobile_number1);
               $sstmt->bindParam(':info_mobile_number2',$info_mobile_number2);
               $sstmt->bindParam(':info_mobile_number3', $info_mobile_number3);
               $sstmt->bindParam(':info_mobile_number4', $info_mobile_number4);
               $sstmt->bindParam(':info_mobile_number5', $info_mobile_number5);
               $sstmt->bindParam(':info_product_type', $info_product_type);   
               $sstmt->bindParam(':info_loan_class', $info_loan_class);   
               $sstmt->bindParam(':info_loan_status', $info_loan_status);   
               $sstmt->bindParam(':info_maturity_date', $info_maturity_date);   
               $sstmt->bindParam(':info_term', $info_term);
               $sstmt->bindParam(':info_remain', $info_remain);
               $sstmt->bindParam(':info_outstanding_balance', $info_outstanding_balance);
               $sstmt->bindParam(':info_loan_total_amount', $info_loan_total_amount);
               $sstmt->bindParam(':info_pn_value', $info_pn_value);
               $sstmt->bindParam(':info_amortization_paid', $info_amortization_paid);
               $sstmt->bindParam(':info_last_principal_payment_date', $info_last_principal_payment_date);
               $sstmt->bindParam(':info_direct_agent', $info_direct_agent);
               $sstmt->bindParam(':info_area', $info_area);
               $sstmt->bindParam(':info_bussiness_address', $info_bussiness_address);
               $sstmt->bindParam(':info_released_date', $info_released_date);
               $sstmt->bindParam(':info_cd_reviewed_by', $info_cd_reviewed_by);
               $sstmt->bindParam(':cron_date', $cron_date);
                            
             $info_renewable_date = $v['Ren_Date'];
             $info_branch = $v['Branch'];
             $info_kiosk = $v['Kiosk'];
             $info_borrower = $v['Borr_Name'];
             $info_pn_number = $v['PN_Number'];
             $info_telephone_number1 = $v['Tel_No1'];
             $info_telephone_number2 = $v['Tel_No2'];
             $info_telephone_number3 = $v['Tel_No3'];
             $info_telephone_number4 = $v['Tel_No4'];
             $info_telephone_number5 = $v['Tel_No5'];
             $info_mobile_number1 = $v['Mob_No1'] ;
             $info_mobile_number2 = $v['Mob_No2'];
             $info_mobile_number3 = $v['Mob_No3'];
             $info_mobile_number4 = $v['Mob_No4'];
             $info_mobile_number5 = $v['Mob_No5'];
             $info_product_type = $v['Prod_Type'];
             $info_loan_class = $v['Loan_Class'];
             $info_loan_status = $v['Loan_Status'];
             $info_maturity_date = $v['Maturity_Date'];
             $info_term = $v['Terms'];
             $info_remain = $v['Remain'];
             $info_outstanding_balance = $v['Oustanding_Bal'];
             $info_loan_total_amount = $v['TLoan_Amount'];
             $info_pn_value = $v['PN_Value'];
             $info_amortization_paid = $v['Amort_Paid'];
             $info_last_principal_payment_date = $v['Last_Principal_Payment_Date'];
             $info_direct_agent = $v['Direct_Agent'];
             $info_area = $v['Area'];
             $info_bussiness_address = $v['Bus_Address'];
             $info_released_date = $v['Released_Date'];
             $info_cd_reviewed_by = $v['CRD_Name'];
             $cron_date = date('Y-m-d H:i:s');


             

               $sstmt->execute();
        


               if($sstmt){

                $ssstmt = $this->connJeon->prepare("INSERT INTO crm_telemarketing(tele_loan_id,
                tele_status,
                tele_note,
                tele_remarks,
                tele_date,
                tele_last_modified_by
                )values(
                :tele_loan_id,
                :tele_status,
                :tele_note,
                :tele_remarks,
                :tele_date,
                :tele_last_modified_by)");    
       
              
        $ssstmt->bindParam(':tele_loan_id', $tele_loan_id);
        $ssstmt->bindParam(':tele_status', $tele_status);
        $ssstmt->bindParam(':tele_note', $tele_note);
        $ssstmt->bindParam(':tele_remarks', $tele_remarks);
        $ssstmt->bindParam(':tele_date', $tele_date);
        $ssstmt->bindParam(':tele_last_modified_by', $tele_last_modified_by);
                                  
              $tele_loan_id = $i;
              $tele_status = $v['Status'];
              $tele_note = $v['Note'];
              $tele_remarks = $v['Remarks'];
              $tele_date = $v['Date'];
              $tele_last_modified_by = $v['Last_Modified_By'];
          
             
 
                $ssstmt->execute();
 
 
                
                $sssstmt = $this->connJeon->prepare("INSERT INTO crm_character_reference(char_loan_id,
                char_name,
                char_contact_no
                )values(
                :char_loan_id,
                :char_name,
                :char_contact_no)");    
 
        $sssstmt->bindParam(':char_loan_id', $char_loan_id);
        $sssstmt->bindParam(':char_name', $char_name);
        $sssstmt->bindParam(':char_contact_no', $char_contact_no);
                                  
              $char_loan_id = $i;
              $char_name = $v['Ref_Name'];
              $char_contact_no = $v['Ref_Contact'];
        
                $sssstmt->execute();
 
 
 
                $ssssstmt = $this->connJeon->prepare("INSERT INTO crm_co_borrower_co_maker(co_loan_id,
                co_co_borrowers,
                co_co_borrower_contact,
                co_co_maker,
                co_co_maker_contact
                )values(
                :co_loan_id,
                :co_co_borrowers,
                :co_co_borrower_contact,
                :co_co_maker,
                :co_co_maker_contact)");    
       
              
        $ssssstmt->bindParam(':co_loan_id', $co_loan_id);
        $ssssstmt->bindParam(':co_co_borrowers', $co_co_borrowers);
        $ssssstmt->bindParam(':co_co_borrower_contact', $co_co_borrower_contact);
        $ssssstmt->bindParam(':co_co_maker', $co_co_maker);
        $ssssstmt->bindParam(':co_co_maker_contact', $co_co_maker_contact);
                                  
              $co_loan_id = $i;
              $co_co_borrowers = $v['Co_Borrower'];
              $co_co_borrower_contact = $v['Co_Borrower_Contact'];
              $co_co_maker = $v['Co_Maker'];
              $co_co_maker_contact = $v['Co_Maker_Contact'];
             
              $ssssstmt->execute();


                  print_r($v['PN_Number']. ' Data has been transfered!');
               }else{
                print_r($v['PN_Number'].' Is not a new lead or duplicated! ');
               }
          $i++;
           }else{
               print_r($v['PN_Number'].' Is not a new lead or duplicated!');
         }
        }
      } 
    }
  }
}
?>