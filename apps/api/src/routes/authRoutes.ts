import { Router } from 'express';
import { z } from 'zod';
import { getEnv } from '../config/env.js';
import { authenticate, SESSION_COOKIE_NAME } from '../middleware/auth.js';
import { getClientIp, rateLimitMiddleware } from '../middleware/rateLimit.js';
import { recordAuditLog } from '../services/auditService.js';
import { loginWithPassword } from '../services/userService.js';

export const authRouter = Router();

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(1),
});

authRouter.post(
  '/login',
  rateLimitMiddleware({
    namespace: 'auth_login',
    maxHits: 15,
    windowSeconds: 300,
    message: 'Too many login attempts. Please try again in a few minutes.',
  }),
  async (req, res, next) => {
    try {
      const parsed = loginSchema.parse(req.body);
      const { token, user } = await loginWithPassword(parsed.email, parsed.password);
      const env = getEnv();

      const ttlMs = 60 * 60 * 12 * 1000;
      res.cookie(SESSION_COOKIE_NAME, token, {
        httpOnly: true,
        sameSite: 'lax',
        secure: env.nodeEnv === 'production',
        maxAge: ttlMs,
        path: '/',
      });

      await recordAuditLog({
        actorId: user.id,
        actorEmail: user.email,
        action: 'auth.login',
        resourceType: 'user_profile',
        resourceId: user.id,
        ipAddress: getClientIp(req),
      });

      res.json({
        success: true,
        data: { token, user },
      });
    } catch (err) {
      next(err);
    }
  }
);

authRouter.post('/logout', authenticate, async (req, res) => {
  res.clearCookie(SESSION_COOKIE_NAME, { path: '/' });
  await recordAuditLog({
    actorId: req.authUser?.id,
    actorEmail: req.authUser?.email,
    action: 'auth.logout',
    resourceType: 'user_profile',
    resourceId: req.authUser?.id,
    ipAddress: getClientIp(req),
  });
  res.json({ success: true });
});

authRouter.get('/me', authenticate, (req, res) => {
  res.json({ success: true, data: { user: req.authUser } });
});
