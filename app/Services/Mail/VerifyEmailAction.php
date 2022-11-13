<?php

  namespace App\Services\Mail;

  use App\Models\User;
  use App\Models\VerifyEmailToken;
  use Carbon\Carbon;
  use Illuminate\Support\Facades\DB;

  class VerifyEmailAction {

    public function handle(string $token)
    {
      $verification = VerifyEmailToken::where('token', $token)->first();
      if (is_null($verification) || date_diff($verification->created_at, Carbon::now())->i > 30) {
        return ['message' => 'Token not valid.', 'isValid' => false];
      }
      return $this->updateEmailVerifiedAt($verification)
        ? ['message' => 'Verify success', 'isValid' => true]
        : ['message' => 'Verify fail', 'isValid' => false];
    }

    private function updateEmailVerifiedAt(VerifyEmailToken $verification)
    {
      try {
        DB::beginTransaction();
        User::where('id', $verification->user_id)->update(['email_verified_at' => Carbon::now()]);
        $verification->delete();
        DB::commit();
      } catch (\Exception $e) {
        DB::rollBack();
//        dd($e->getMessage());
        return false;
      }
      return true;
    }
  }