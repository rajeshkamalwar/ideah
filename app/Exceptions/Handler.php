<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Str;
use Throwable;

class Handler extends ExceptionHandler
{
  /**
   * A list of the exception types that are not reported.
   *
   * @var array
   */
  protected $dontReport = [
    //
  ];

  /**
   * A list of the inputs that are never flashed for validation exceptions.
   *
   * @var array
   */
  protected $dontFlash = [
    'current_password',
    'password',
    'password_confirmation',
  ];

  /**
   * Register the exception handling callbacks for the application.
   *
   * @return void
   */
  public function register()
  {
    $this->reportable(function (Throwable $e) {
      //
    });
  }

  /**
   * Render database-related failures as a simple page (no DB, no frontend layout).
   * Prevents cascading 500s when error views also query MySQL (e.g. old 404 template).
   */
  public function render($request, Throwable $e)
  {
    if (!$request->expectsJson() && $this->isDatabaseConnectionFailure($e)) {
      return response()->view('errors.database-unavailable', [
        'message' => config('app.debug') ? $e->getMessage() : null,
      ], 503);
    }

    return parent::render($request, $e);
  }

  protected function isDatabaseConnectionFailure(Throwable $e): bool
  {
    if ($e instanceof QueryException) {
      return true;
    }

    if ($e->getPrevious() instanceof QueryException) {
      return true;
    }

    $msg = $e->getMessage();
    if (Str::contains($msg, ['SQLSTATE', 'could not find driver', 'Connection refused', 'Access denied for user', 'Unknown database'])) {
      return true;
    }

    $prev = $e->getPrevious();
    if ($prev instanceof Throwable) {
      return $this->isDatabaseConnectionFailure($prev);
    }

    return false;
  }
}
