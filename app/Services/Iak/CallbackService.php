<?php

namespace App\Services\Iak;

class CallbackService extends Iak
{
    /**
     * ================================================================
     * VALIDASI CALLBACK IAK
     * ================================================================
     *
     * Signature callback IAK:
     *
     * MD5(username + api_key + ref_id)
     */
    public function isSignatureValid(
        array $payload
    ): bool {
        $refId = $payload['ref_id'] ?? null;
        $sign = $payload['sign'] ?? null;

        /**
         * ref_id dan sign wajib ada.
         */
        if (! $refId || ! $sign) {
            return false;
        }

        /**
         * Generate signature yang seharusnya.
         */
        $expectedSignature = $this->sign($refId);

        /**
         * Bandingkan dengan signature dari IAK.
         */
        return hash_equals(
            $expectedSignature,
            $sign
        );
    }
}
