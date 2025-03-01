<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\BillPaymentLog;
use Illuminate\Http\Request;

class PaymentValidationController extends Controller
{
    public function validatePayment(Request $request)
    {
        $payeeId = $request->query('PAYEE_ID');
        //$paymentType = $request->query('PAYMENT_TYPE');
        if (empty($payeeId)) {
          return  response("PAYEE_ID=$payeeId&~PAYMENT_TYPE=LUC&~FeeStatus=PAYEE ID is Empty");
        }
        $bill = Billing::where('assessment_no', $payeeId)
            ->where('objection', 0)
            ->where('status', 4) //approved
            //->where('paid', 0)
            ->first();
        if(empty($bill)){
            return response()->json([
                "FeeRequest" => [
                    "PayeeID" => $payeeId,
                    "FeeStatus" => "Invalid Payee ID "
                ]
            ]);
        }

           /* if ($bill->status != 4) { //approved
                return response()->json([
                    "FeeRequest" => [
                        "PayeeID" => $payeeId,
                        "FeeStatus" => "This Payee ID is awaiting approval or archived "
                    ]
                ]);
            }*/



        if ($bill->paid == 1) {
            return response()->json([
                "FeeRequest" => [
                    "PayeeName" => $bill->getPropertyList->owner_name ?? 'Owner Name',
                    "PayeeID" => $bill->assessment_no,
                    "Amount" => number_format($bill->bill_amount, 2),
                    "FeeStatus" => "Assessment Already Paid",
                    "Email" => $bill->email,
                    "PhoneNumber" => $bill->getPropertyList->owner_gsm ?? '234'
                ]
            ]);
        }
        return response()->json([
            "FeeRequest" => [
                "PayeeName" => $bill->getPropertyList->owner_name ?? 'Owner Name',
                "PayeeID" => $bill->assessment_no,
                "Amount" => number_format($bill->bill_amount, 2),
                "FeeStatus" => "Fee has not yet been paid",
                "Email" => $bill->email,
                "PhoneNumber" => $bill->getPropertyList->owner_gsm ?? '234'
            ]
        ]);
    }


    public function notifyETranzact(Request $request){
        $clientIp = $request->ip();
        $allowedIps = [
            '192.168.1.10',
            '203.0.113.45',
            '127.0.0.1'
        ];
        if (!in_array($clientIp, $allowedIps)) {
            return response("false -1", 403);
        }

        $receiptNo = $request->query('RECEIPT_NO');
        $paymentCode = $request->query('PAYMENT_CODE');
        $transAmount = $request->query('TRANS_AMOUNT');
        $assessmentNo = $request->query('CUSTOMER_ID');
        if (empty($receiptNo)  || empty($transAmount) || empty($assessmentNo)) {
            return  response("false 2");
        }
        $bankName = $request->query('BANK_NAME');
        $branch = $request->query('BRANCH_NAME');
        $payMode = "eTranzact";
        $phoneNumber = "COL11";
        $customerName = $request->query('CUSTOMER_NAME');
        $email = "";
        $kgTin = "";
        if(!is_numeric($transAmount)){
            return  response("false 4");
        }
        $payment = BillPaymentLog::where('receipt_no', $receiptNo)->first();
        if(!empty($payment)){
            return  response("false 1");
        }

        $bill = Billing::where('assessment_no', $assessmentNo)
            ->where('paid', 0)
            ->first();
        if (empty($bill)) {
            return response()->json([
                "FeeRequest" => [
                    "Customer ID" => $assessmentNo,
                    "FeeStatus" => "Invalid Customer ID "
                ]
            ]);
        }else{
            $billList = Billing::where('building_code', $bill->building_code)
                ->where('year','<=', $bill->year)
                ->where('paid', 0)
                ->orderBy('id', 'ASC')
                ->get();
            if(!empty($billList)){
                foreach ($billList as $item){
                    $balance = $item->bill_amount - $item->paid_amount;
                    $transBalance = $transAmount - $balance;
                    if($balance > 0){
                        if($transAmount > $balance){
                                $item->paid_amount += $balance;
                                $item->payment_ref = $paymentCode;
                                $item->paid = 1;
                                $item->date_paid = now();
                                $item->paid_by = 1;
                                $item->save();
                                $this->_registerInPaymentLog($item->id, 1, $balance, $receiptNo, $paymentCode,
                                           $assessmentNo, $bankName, $branch, $payMode, $customerName,
                                           $email, $kgTin);
                        }
                        else{
                            if($transAmount > 0){
                                $item->paid_amount += $transAmount;
                                $item->payment_ref = $paymentCode;
                                $item->save();
                                if($item->paid_amount == $item->bill_amount){
                                    $item->paid = 1;
                                    $item->date_paid = now();
                                    $item->paid_by = 1;
                                    $item->save();
                                }
                                $this->_registerInPaymentLog($item->id, 1, $transAmount, $receiptNo, $paymentCode,
                                    $assessmentNo, $bankName, $branch, $payMode, $customerName,
                                    $email, $kgTin);
                            }
                        }
                    }
                    $transAmount = $transBalance;
                }
            }
        }

        return response()->json(['message'=>'Payment done'],200);
    }


    private function _registerInPaymentLog($billMasterId, $paidBy = 1, $amount, $receiptNo, $paymentCode,
                                           $assessmentNo, $bankName, $branchName, $payMode, $customerName,
                                           $email, $kgTin){
        BillPaymentLog::create([
            "bill_master"=>$billMasterId,
            "paid_by"=>$paidBy,
            "amount"=>$amount,
            "receipt_no"=>$receiptNo,
            "payment_code"=>$paymentCode,
            "assessment_no"=>$assessmentNo,
            "bank_name"=>$bankName,
            "branch_name"=>$branchName,
            "pay_mode"=>$payMode,
            "customer_name"=>$customerName,
            "email"=>$email,
            "kgtin"=>$kgTin,
        ]);
    }

}
