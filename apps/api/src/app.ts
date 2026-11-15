import fs from 'node:fs';
import path from 'node:path';
import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import cookieParser from 'cookie-parser';
import { getEnv } from './config/env.js';
import { errorHandler } from './middleware/errorHandler.js';
import { publicRouter } from './routes/publicRoutes.js';
import { authRouter } from './routes/authRoutes.js';
import { adminRouter } from './routes/adminRoutes.js';
import { generateRobotsTxt, generateSitemapXml } from './services/seoService.js';

export function createApp() {
  const env = getEnv();
  const app = express();

  app.set('trust proxy', 1);

  app.use(
    helmet({
      contentSecurityPolicy: false,
      crossOriginResourcePolicy: { policy: 'cross-origin' },
    })
  );

  app.use(
    cors({
      origin: (origin, cb) => {
        if (!origin) return cb(null, true);
        if (origin === env.appOrigin || origin === env.publicSiteUrl) {
          return cb(null, true);
        }
        return cb(null, true); // Allow preview e2b.app origins dynamically in sandboxed deployments
      },
      credentials: true,
    })
  );

  app.use(cookieParser());

  // Preserve raw body for Razorpay webhook HMAC verification
  app.use(
    '/api/v1/webhooks/razorpay',
    express.raw({ type: 'application/json', limit: '2mb' })
  );

  app.use(express.json({ limit: '5mb' }));
  app.use(express.urlencoded({ extended: true, limit: '5mb' }));

  // Serve local uploaded media assets when Supabase Storage is not active
  const uploadDir = path.resolve(process.cwd(), '.uploads');
  fs.mkdirSync(uploadDir, { recursive: true });
  app.use('/uploads', express.static(uploadDir));

  // Legacy WordPress compatibility redirects
  app.get('/polymer-products', (_req, res) => res.redirect(301, '/products/'));
  app.get('/polymer-products/', (_req, res) => res.redirect(301, '/products/'));
  app.get('/polymer-products/:slug', (req, res) =>
    res.redirect(301, `/products/${req.params.slug}/`)
  );
  app.get('/polymer-products/:slug/', (req, res) =>
    res.redirect(301, `/products/${req.params.slug}/`)
  );

  // Dynamic SEO endpoints
  app.get('/sitemap.xml', async (req, res, next) => {
    try {
      const origin = `${req.protocol}://${req.get('host')}`;
      const xml = await generateSitemapXml(origin);
      res.type('application/xml').send(xml);
    } catch (err) {
      next(err);
    }
  });

  app.get('/robots.txt', async (req, res, next) => {
    try {
      const origin = `${req.protocol}://${req.get('host')}`;
      const txt = await generateRobotsTxt(origin);
      res.type('text/plain').send(txt);
    } catch (err) {
      next(err);
    }
  });

  // API v1 routers
  app.use('/api/v1', publicRouter);
  app.use('/api/v1/auth', authRouter);
  app.use('/api/v1/admin', adminRouter);

  // Serve built frontend bundle in production if present
  const webDistDir = path.resolve(process.cwd(), '../web/dist');
  if (fs.existsSync(webDistDir)) {
    app.use(express.static(webDistDir));
    app.get(/^(?!\/api\/|\/uploads\/).*/, (_req, res) => {
      res.sendFile(path.join(webDistDir, 'index.html'));
    });
  }

  app.use(errorHandler);
  return app;
}
