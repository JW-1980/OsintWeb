import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

/**
 * Feature categories for analytics tracking.
 */
export const FeatureCategories = {
  NAVIGATION: 'navigation',
  CONTENT: 'content',
  TOOLS: 'tools',
  EXPORT: 'export',
  MAP: 'map',
  FILTER: 'filter',
  SEARCH: 'search',
  MEDIA: 'media',
  SOCIAL: 'social',
  ADMIN: 'admin',
} as const;

export type FeatureCategory = typeof FeatureCategories[keyof typeof FeatureCategories];

/**
 * Common feature actions.
 */
export const FeatureActions = {
  VIEW: 'view',
  CLICK: 'click',
  USE: 'use',
  EXPAND: 'expand',
  COLLAPSE: 'collapse',
  SUBMIT: 'submit',
  DOWNLOAD: 'download',
  SHARE: 'share',
  FILTER: 'filter',
  SORT: 'sort',
  PLAY: 'play',
  PAUSE: 'pause',
  ZOOM: 'zoom',
  PAN: 'pan',
} as const;

export type FeatureAction = typeof FeatureActions[keyof typeof FeatureActions];

interface TrackingEvent {
  type: 'feature' | 'search';
  feature?: string;
  category?: string;
  action?: string;
  metadata?: Record<string, any>;
  term?: string;
  search_type?: string;
  result_count?: number;
  timestamp: string;
}

class AnalyticsTracker {
  private queue: TrackingEvent[] = [];
  private flushTimeout: ReturnType<typeof setTimeout> | null = null;
  private batchSize = 15;
  private flushInterval = 60000; // 60 seconds
  private isEnabled = true;

  constructor() {
    // Check if analytics should be enabled (respect Do Not Track)
    if (typeof navigator !== 'undefined' && navigator.doNotTrack === '1') {
      this.isEnabled = false;
    }
  }

  /**
   * Enable or disable tracking.
   */
  setEnabled(enabled: boolean): void {
    this.isEnabled = enabled;
    if (!enabled) {
      this.queue = [];
      if (this.flushTimeout) {
        clearTimeout(this.flushTimeout);
        this.flushTimeout = null;
      }
    }
  }

  /**
   * Track a feature usage event.
   */
  trackFeature(
    feature: string,
    category: FeatureCategory,
    action: FeatureAction | string,
    metadata?: Record<string, any>
  ): void {
    if (!this.isEnabled) return;

    this.queue.push({
      type: 'feature',
      feature,
      category,
      action,
      metadata,
      timestamp: new Date().toISOString(),
    });

    this.checkFlush();
  }

  /**
   * Track a search event.
   */
  trackSearch(term: string, searchType: string, resultCount: number): void {
    if (!this.isEnabled) return;

    this.queue.push({
      type: 'search',
      term,
      search_type: searchType,
      result_count: resultCount,
      timestamp: new Date().toISOString(),
    });

    this.checkFlush();
  }

  /**
   * Check if we should flush the queue.
   */
  private checkFlush(): void {
    if (this.queue.length >= this.batchSize) {
      this.flush();
    } else {
      this.scheduleFlush();
    }
  }

  /**
   * Schedule a flush.
   */
  private scheduleFlush(): void {
    if (this.flushTimeout) return;

    this.flushTimeout = setTimeout(() => {
      this.flush();
    }, this.flushInterval);
  }

  /**
   * Flush all queued events to the server.
   */
  async flush(): Promise<void> {
    if (!this.isEnabled || this.queue.length === 0) return;

    if (this.flushTimeout) {
      clearTimeout(this.flushTimeout);
      this.flushTimeout = null;
    }

    const events = [...this.queue];
    this.queue = [];

    try {
      await axios.post('/api/track/batch', { events });
    } catch (error) {
      // Re-queue on failure (up to a limit)
      if (this.queue.length < 100) {
        this.queue = [...events, ...this.queue];
      }
      console.warn('Failed to send analytics batch:', error);
    }
  }
}

// Singleton instance
let trackerInstance: AnalyticsTracker | null = null;

/**
 * Composable for anonymous site analytics tracking.
 *
 * Tracks feature usage and search behavior without storing personal data.
 * Respects Do Not Track browser setting.
 */
export function useAnalytics() {
  if (!trackerInstance) {
    trackerInstance = new AnalyticsTracker();
  }

  const isEnabled = ref(true);

  /**
   * Track feature usage.
   */
  const trackFeature = (
    feature: string,
    category: FeatureCategory,
    action: FeatureAction | string,
    metadata?: Record<string, any>
  ) => {
    trackerInstance!.trackFeature(feature, category, action, metadata);
  };

  /**
   * Track search events.
   */
  const trackSearch = (term: string, searchType: string, resultCount: number) => {
    trackerInstance!.trackSearch(term, searchType, resultCount);
  };

  // Convenience tracking methods

  /**
   * Track navigation to a page/section.
   */
  const trackNavigation = (destination: string, metadata?: Record<string, any>) => {
    trackFeature(destination, FeatureCategories.NAVIGATION, FeatureActions.CLICK, metadata);
  };

  /**
   * Track tool usage.
   */
  const trackToolUse = (toolName: string, action: string, metadata?: Record<string, any>) => {
    trackFeature(toolName, FeatureCategories.TOOLS, action, metadata);
  };

  /**
   * Track map interaction.
   */
  const trackMapInteraction = (action: string, metadata?: Record<string, any>) => {
    trackFeature('map', FeatureCategories.MAP, action, metadata);
  };

  /**
   * Track filter usage.
   */
  const trackFilter = (filterName: string, value: any) => {
    trackFeature(filterName, FeatureCategories.FILTER, FeatureActions.FILTER, { value });
  };

  /**
   * Track content view.
   */
  const trackContentView = (contentType: string, contentId: string | number) => {
    trackFeature(contentType, FeatureCategories.CONTENT, FeatureActions.VIEW, { id: contentId });
  };

  /**
   * Track export action.
   */
  const trackExport = (exportType: string, format: string) => {
    trackFeature(exportType, FeatureCategories.EXPORT, FeatureActions.DOWNLOAD, { format });
  };

  /**
   * Track share action.
   */
  const trackShare = (contentType: string, platform?: string) => {
    trackFeature(contentType, FeatureCategories.SOCIAL, FeatureActions.SHARE, { platform });
  };

  /**
   * Track media playback.
   */
  const trackMediaPlay = (mediaType: string, mediaId: string | number) => {
    trackFeature(mediaType, FeatureCategories.MEDIA, FeatureActions.PLAY, { id: mediaId });
  };

  /**
   * Enable or disable tracking.
   */
  const setEnabled = (enabled: boolean) => {
    isEnabled.value = enabled;
    trackerInstance!.setEnabled(enabled);
  };

  /**
   * Flush pending events (call on page unload).
   */
  const flush = () => trackerInstance!.flush();

  // Auto-flush on page unload
  onMounted(() => {
    if (typeof window !== 'undefined') {
      window.addEventListener('beforeunload', flush);
      window.addEventListener('pagehide', flush);
    }
  });

  onUnmounted(() => {
    if (typeof window !== 'undefined') {
      window.removeEventListener('beforeunload', flush);
      window.removeEventListener('pagehide', flush);
    }
  });

  return {
    // State
    isEnabled,

    // Core methods
    trackFeature,
    trackSearch,
    flush,
    setEnabled,

    // Convenience methods
    trackNavigation,
    trackToolUse,
    trackMapInteraction,
    trackFilter,
    trackContentView,
    trackExport,
    trackShare,
    trackMediaPlay,

    // Constants
    FeatureCategories,
    FeatureActions,
  };
}
