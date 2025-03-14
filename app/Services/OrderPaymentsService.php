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

        $item = $this->repository->findWithInputs(['transaction_id' => $transactionId], ['order']);
        if (!$item) {
            throw new Exception(__("custom.defaults.not_found"));
        }

        $amount = $item->amount;

        $result = [
            'status' => 'failed',
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'reference_id' => '',
        ];

        DB::beginTransaction();
        try {

            $receipt = Payment::amount($amount)->transactionId($transactionId)->verify();
            $referenceId = $receipt->getReferenceId();

            $orderPaymentInputs = [
                'status' => 'completed',
                'reference_id' => $referenceId,
                'payment_details' => json_encode($receipt->getDetails()),
            ];
            $this->repository->update($item, $orderPaymentInputs);

            $item->order()->update([
                'progress_status' => 'waitingForConfirm'
            ]);

            DB::commit();

            $result['status'] = 'success';
            $result['reference_id'] = $referenceId;

        } catch (InvalidPaymentException $exception) {
            $orderPaymentInputs = [
                'status' => 'failed',
                'payment_details' => json_encode([
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage()
                ]),
            ];
            $this->repository->update($item, $orderPaymentInputs);

            DB::commit();

        } catch (Exception) {
            $orderPaymentInputs = [
                'status' => 'failed',
                'payment_details' => json_encode([
                    'code' => 500,
                    'message' => __("custom.defaults.index_failed")
                ]),
            ];
            $this->repository->update($item, $orderPaymentInputs);

            DB::commit();
        }

        return $result;
    }

}
