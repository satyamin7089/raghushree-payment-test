import { query } from '../db/client.js';

export interface AuditEntryInput {
  actorId?: string | null;
  actorEmail?: string;
  action: string;
  resourceType: string;
  resourceId?: string;
  metadata?: Record<string, unknown>;
  ipAddress?: string;
}

export async function recordAuditLog(entry: AuditEntryInput): Promise<void> {
  try {
    await query(
      `INSERT INTO public.audit_logs (actor_id, actor_email, action, resource_type, resource_id, metadata, ip_address)
       VALUES ($1, $2, $3, $4, $5, $6::jsonb, $7)`,
      [
        entry.actorId || null,
        entry.actorEmail || 'system',
        entry.action,
        entry.resourceType,
        entry.resourceId || '',
        JSON.stringify(entry.metadata || {}),
        entry.ipAddress || '',
      ]
    );
  } catch (err) {
    console.error('[auditService] Failed to record audit log:', err);
  }
}

export async function listAuditLogs(limit = 100, offset = 0) {
  const res = await query(
    `SELECT id, actor_id, actor_email, action, resource_type, resource_id, metadata, ip_address, created_at
     FROM public.audit_logs
     ORDER BY created_at DESC
     LIMIT $1 OFFSET $2`,
    [Math.min(200, Math.max(1, limit)), Math.max(0, offset)]
  );
  return res.rows;
}
