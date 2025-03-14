<?php

namespace App\Services;

use App\Http\Repositories\OrderPaymentRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Facade\Payment;

class OrderPaymentsService
{

    public function __construct(public OrderPaymentRepository $repository)
    {
    }


    /**
     * @throws Exception
     */
    public function verifyPayment($inputs): array
    {
        $transactionId = $inputs['Authority'];

        $orderPayment = $this->repository->findWithInputs(['transaction_id' => $transactionId], ['order']);
        if (!$orderPayment) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        $amount = $orderPayment->amount;

        $result = [
            'status' => 'failed',
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'reference_id' => null,
            'message' => null,
        ];

        DB::beginTransaction();
        try {

            if ($orderPayment->order->progress_status != 'pendingForPayment') {
                throw new Exception(__("custom.shop.not_access_pay_order_progress_status"));
            }

            $receipt = Payment::amount($amount)->transactionId($transactionId)->verify();
            $referenceId = $receipt->getReferenceId();

            $orderPaymentInputs = [
                'status' => 'completed',
                'reference_id' => $referenceId,
                'payment_details' => json_encode($receipt->getDetails()),
            ];
            $this->repository->update($orderPayment, $orderPaymentInputs);

            $orderPayment->order()->update([
                'progress_status' => 'waitingForConfirm'
            ]);

            DB::commit();

            $result['status'] = 'success';
            $result['reference_id'] = $referenceId;
            $result['message'] = __("custom.shop.payment_order_successfully");

        } catch (InvalidPaymentException $exception) {
            $message = $exception->getMessage();
            $orderPaymentInputs = [
                'status' => 'failed',
                'payment_details' => json_encode([
                    'code' => $exception->getCode(),
                    'message' => $message
                ]),
            ];
            $this->repository->update($orderPayment, $orderPaymentInputs);

            $result['message'] = $message;

            DB::commit();


        } catch (Exception $exception) {
            $message = $exception->getMessage();
            if (!is_string_persian($message)) {
                $message = __("custom.defaults.index_failed");
            }
            $orderPaymentInputs = [
                'status' => 'failed',
                'payment_details' => json_encode([
                    'code' => 500,
                    'message' => $message
                ]),
            ];
            $this->repository->update($orderPayment, $orderPaymentInputs);

            $result['message'] = $message;

            DB::commit();
        }

        return $result;
    }

}
