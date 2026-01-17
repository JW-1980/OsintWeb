import { ref } from 'vue';
import { useApi } from './useApi';

export interface ActivityLogEntry {
  activity_type: string;
  description?: string;
  properties?: Record<string, any>;
  timestamp?: string;
}

export interface PublicActivity {
  id: number;
  user: {
    id: number;
    name: string;
    avatar_url?: string;
  } | null;
  activity_type: string;
  description: string;
  icon: string;
  properties?: Record<string, any>;
  created_at: string;
  time_ago: string;
}

export interface ActivityStats {
  contributions_today: number;
  contributions_week: number;
  active_contributors_today: number;
  events_created_week: number;
  events_verified_week: number;
  sources_verified_week: number;
}

// Activity type constants matching backend
export const ActivityTypes = {
  // Page views
  PAGE_VIEW: 'page_view',
  MAP_VIEW: 'map_view',
  MAP_INTERACTION: 'map_interaction',
  SEARCH: 'search',
  FILTER_APPLY: 'filter_apply',
  EXPORT_REQUEST: 'export_request',
  SHARE: 'share',

  // Content views
  CONFLICT_VIEW: 'conflict_view',
  ACTOR_VIEW: 'actor_view',
  EQUIPMENT_VIEW: 'equipment_view',
  ZONE_VIEW: 'zone_view',
  SOURCE_VIEW: 'source_view',
  TIMELINE_VIEW: 'timeline_view',
  REPORT_VIEW: 'report_view',
  EVENT_VIEW: 'event_view',
  ARTICLE_VIEW: 'article_view',

  // Contributions
  EVENT_CREATE: 'event_create',
  EVENT_EDIT: 'event_edit',
  EVENT_VERIFY: 'event_verify',
  EVENT_DISPUTE: 'event_dispute',
  SOURCE_CREATE: 'source_create',
  SOURCE_VERIFY: 'source_verify',
  EVIDENCE_SUBMIT: 'evidence_submit',
  TIP_SUBMIT: 'tip_submit',
  COMMENT_CREATE: 'comment_create',
} as const;

export type ActivityType = typeof ActivityTypes[keyof typeof ActivityTypes];

class ActivityLogger {
  private api = useApi();
  private queue: ActivityLogEntry[] = [];
  private flushTimeout: ReturnType<typeof setTimeout> | null = null;
  private batchSize = 10;
  private flushInterval = 30000; // 30 seconds

  /**
   * Log a single activity immediately.
   */
  async log(activityType: ActivityType, description?: string, properties?: Record<string, any>): Promise<void> {
    try {
      await this.api.post('/activity/log', {
        activity_type: activityType,
        description,
        properties,
      });
    } catch (error) {
      console.error('Failed to log activity:', error);
      // Queue for retry
      this.queueActivity({ activity_type: activityType, description, properties });
    }
  }

  /**
   * Queue an activity for batch sending.
   */
  queueActivity(entry: ActivityLogEntry): void {
    this.queue.push({
      ...entry,
      timestamp: new Date().toISOString(),
    });

    // Flush if batch is full
    if (this.queue.length >= this.batchSize) {
      this.flush();
    } else {
      // Schedule flush
      this.scheduleFlush();
    }
  }

  /**
   * Schedule a flush of queued activities.
   */
  private scheduleFlush(): void {
    if (this.flushTimeout) return;

    this.flushTimeout = setTimeout(() => {
      this.flush();
    }, this.flushInterval);
  }

  /**
   * Flush all queued activities to the server.
   */
  async flush(): Promise<void> {
    if (this.queue.length === 0) return;

    if (this.flushTimeout) {
      clearTimeout(this.flushTimeout);
      this.flushTimeout = null;
    }

    const activities = [...this.queue];
    this.queue = [];

    try {
      await this.api.post('/activity/log/batch', { activities });
    } catch (error) {
      console.error('Failed to flush activity queue:', error);
      // Re-queue failed activities
      this.queue = [...activities, ...this.queue];
    }
  }

  /**
   * Get public activity feed.
   */
  async getPublicFeed(limit = 20, days = 7): Promise<PublicActivity[]> {
    const response = await this.api.get<{ data: PublicActivity[] }>('/activity/feed', {
      params: { limit, days },
    });
    return response.data;
  }

  /**
   * Get activity statistics.
   */
  async getStats(): Promise<ActivityStats> {
    const response = await this.api.get<{ data: ActivityStats }>('/activity/stats');
    return response.data;
  }

  /**
   * Get current user's activity history.
   */
  async getMyActivity(page = 1, perPage = 25): Promise<any> {
    return this.api.get('/activity/my', {
      params: { page, per_page: perPage },
    });
  }
}

let loggerInstance: ActivityLogger | null = null;

/**
 * Composable for activity logging.
 */
export function useActivityLog() {
  if (!loggerInstance) {
    loggerInstance = new ActivityLogger();
  }

  const publicFeed = ref<PublicActivity[]>([]);
  const stats = ref<ActivityStats | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  /**
   * Log a page view.
   */
  const logPageView = (pageName: string, properties?: Record<string, any>) => {
    loggerInstance!.queueActivity({
      activity_type: ActivityTypes.PAGE_VIEW,
      description: `Visited ${pageName}`,
      properties: { page: pageName, ...properties },
    });
  };

  /**
   * Log a map interaction.
   */
  const logMapInteraction = (action: string, properties?: Record<string, any>) => {
    loggerInstance!.queueActivity({
      activity_type: ActivityTypes.MAP_INTERACTION,
      description: action,
      properties,
    });
  };

  /**
   * Log a search.
   */
  const logSearch = (query: string, resultCount?: number) => {
    loggerInstance!.queueActivity({
      activity_type: ActivityTypes.SEARCH,
      description: `Searched for "${query}"`,
      properties: { query, result_count: resultCount },
    });
  };

  /**
   * Log viewing content.
   */
  const logContentView = (contentType: string, contentId: string | number, contentName?: string) => {
    const typeMap: Record<string, ActivityType> = {
      event: ActivityTypes.EVENT_VIEW,
      conflict: ActivityTypes.CONFLICT_VIEW,
      actor: ActivityTypes.ACTOR_VIEW,
      equipment: ActivityTypes.EQUIPMENT_VIEW,
      zone: ActivityTypes.ZONE_VIEW,
      source: ActivityTypes.SOURCE_VIEW,
      article: ActivityTypes.ARTICLE_VIEW,
    };

    const activityType = typeMap[contentType.toLowerCase()] || ActivityTypes.PAGE_VIEW;

    loggerInstance!.queueActivity({
      activity_type: activityType,
      description: contentName ? `Viewed ${contentName}` : `Viewed ${contentType} #${contentId}`,
      properties: { content_type: contentType, content_id: contentId, content_name: contentName },
    });
  };

  /**
   * Log a share action.
   */
  const logShare = (contentType: string, contentId: string | number, platform?: string) => {
    loggerInstance!.log(ActivityTypes.SHARE, `Shared ${contentType}`, {
      content_type: contentType,
      content_id: contentId,
      platform,
    });
  };

  /**
   * Fetch public activity feed.
   */
  const fetchPublicFeed = async (limit = 20, days = 7) => {
    loading.value = true;
    error.value = null;
    try {
      publicFeed.value = await loggerInstance!.getPublicFeed(limit, days);
    } catch (e: any) {
      error.value = e.message || 'Failed to fetch activity feed';
    } finally {
      loading.value = false;
    }
  };

  /**
   * Fetch activity statistics.
   */
  const fetchStats = async () => {
    try {
      stats.value = await loggerInstance!.getStats();
    } catch (e: any) {
      console.error('Failed to fetch activity stats:', e);
    }
  };

  /**
   * Flush pending activities (call on page unload).
   */
  const flush = () => loggerInstance!.flush();

  return {
    // State
    publicFeed,
    stats,
    loading,
    error,

    // Methods
    log: loggerInstance!.log.bind(loggerInstance),
    logPageView,
    logMapInteraction,
    logSearch,
    logContentView,
    logShare,
    fetchPublicFeed,
    fetchStats,
    flush,

    // Constants
    ActivityTypes,
  };
}
