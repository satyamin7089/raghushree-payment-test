import type { Request, Response, NextFunction } from 'express';
import { ZodError } from 'zod';
import { isAppError } from '../utils/errors.js';

export function errorHandler(err: unknown, _req: Request, res: Response, _next: NextFunction) {
  if (err instanceof ZodError) {
    return res.status(400).json({
      success: false,
      error: {
        code: 'validation_error',
        message: err.issues[0]?.message || 'Validation failed.',
        issues: err.issues.map((i) => ({ path: i.path.join('.'), message: i.message })),
      },
    });
  }

  if (isAppError(err)) {
    return res.status(err.statusCode).json({
      success: false,
      error: {
        code: err.code,
        message: err.message,
        ...(err.details ? { details: err.details } : {}),
      },
    });
  }

  console.error('[API Error]', err);
  return res.status(500).json({
    success: false,
    error: {
      code: 'internal_error',
      message: 'An unexpected server error occurred.',
    },
  });
}
