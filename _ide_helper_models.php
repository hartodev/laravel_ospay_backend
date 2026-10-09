<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property array|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AppSetting whereValue($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAppSetting {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $agent_id
 * @property int $transaction_id
 * @property int $amount
 * @property string $status
 * @property string|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Commission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCommission {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $product_id
 * @property string $role
 * @property string $type
 * @property string $value
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $valid_from
 * @property \Illuminate\Support\Carbon|null $valid_until
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Products|null $product
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule active()
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommissionRule whereValue($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCommissionRule {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $agent_id
 * @property int $transaction_id
 * @property int $amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $agent
 * @property-read \App\Models\Transactions $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions query()
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Commissions whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCommissions {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string|null $intro
 * @property array $sections
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereIntro($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereSections($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContentPage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperContentPage {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $transaction_number
 * @property int $amount
 * @property string $payment_method
 * @property string|null $qr_url
 * @property string $status
 * @property array|null $midtrans_response
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits query()
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereMidtransResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereQrUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereTransactionNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Deposits whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperDeposits {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $question
 * @property string $answer
 * @property int $sort_order
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Faq newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq query()
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereAnswer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereQuestion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Faq whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperFaq {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $category
 * @property string $product_code
 * @property string|null $brand
 * @property string|null $type
 * @property string $name
 * @property string|null $description
 * @property int $base_price
 * @property int $price_user
 * @property int $price_agent
 * @property int|null $price_guest
 * @property string $status
 * @property array|null $raw_response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CommissionRule> $commissionRules
 * @property-read int|null $commission_rules_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transactions> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Products newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Products newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Products query()
 * @method static \Illuminate\Database\Eloquent\Builder|Products sellable()
 * @method static \Illuminate\Database\Eloquent\Builder|Products syncedFromIak()
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products wherePriceAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products wherePriceGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products wherePriceUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereProductCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereRawResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Products whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperProducts {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property string $ref_id
 * @property string $customer_id
 * @property string $payment_method
 * @property string|null $midtrans_order_id
 * @property int $base_price
 * @property int $sell_price
 * @property string|null $payment_status
 * @property string $status
 * @property string|null $sn
 * @property string|null $failure_reason
 * @property array|null $iak_response
 * @property string|null $qr_url
 * @property array|null $midtrans_response
 * @property string $refund_status
 * @property int|null $refund_amount
 * @property string|null $refund_reason
 * @property \Illuminate\Support\Carbon|null $refunded_at
 * @property \Illuminate\Support\Carbon|null $checked_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Products $product
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereCheckedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereFailureReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereIakResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereMidtransOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereMidtransResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereQrUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereRefId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereRefundAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereRefundReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereRefundStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereRefundedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereSellPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereSn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transactions whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTransactions {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $store_name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string $role
 * @property string $status
 * @property int|null $parent_agent_id
 * @property string|null $phone
 * @property string|null $subdomain
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Commissions> $commissions
 * @property-read int|null $commissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Deposits> $deposits
 * @property-read int|null $deposits_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read User|null $parentAgent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $subUsers
 * @property-read int|null $sub_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transactions> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\Wallets|null $wallet
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereParentAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereStoreName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSubdomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $wallet_id
 * @property string $type
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property int $amount
 * @property int $balance_before
 * @property int $balance_after
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Wallets $wallet
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereBalanceAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereBalanceBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WalletTransaction whereWalletId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperWalletTransaction {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $balance
 * @property int $held_balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $available_balance
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WalletTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets query()
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets whereHeldBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wallets whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperWallets {}
}

