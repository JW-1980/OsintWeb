<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Function to calculate actor priority score
        DB::statement("
            CREATE OR REPLACE FUNCTION calculate_actor_priority_score(p_actor_id INTEGER)
            RETURNS DECIMAL(5,2) AS $$
            DECLARE
                v_score DECIMAL(5,2) := 0;
                v_conflict_score DECIMAL(5,2) := 0;
                v_activity_score DECIMAL(5,2) := 0;
                v_recency_score DECIMAL(5,2) := 0;
                v_type_score DECIMAL(5,2) := 0;
                v_significance_score DECIMAL(5,2) := 0;
                v_conflict_count INTEGER := 0;
            BEGIN
                -- 1. Active Conflict Participation Score (0-40 points)
                SELECT
                    COUNT(*) as conflict_count,
                    COALESCE(SUM(
                        CASE c.intensity_level
                            WHEN 'high' THEN 40
                            WHEN 'medium' THEN 25
                            WHEN 'low' THEN 10
                            ELSE 0
                        END
                    ), 0) as total_conflict_score
                INTO v_conflict_count, v_conflict_score
                FROM conflict_parties cp
                JOIN conflicts c ON cp.conflict_id = c.id
                WHERE cp.actor_id = p_actor_id
                AND cp.is_currently_active = true
                AND c.is_active = true
                AND c.deleted_at IS NULL;

                -- Cap base conflict score at 40, add 5 points per additional conflict
                IF v_conflict_score > 40 THEN
                    v_conflict_score := 40 + LEAST((v_conflict_count - 1) * 5, 15);
                END IF;

                -- 2. Activity Level Score (0-20 points)
                SELECT
                    CASE a.activity_level
                        WHEN 'high' THEN 20
                        WHEN 'medium' THEN 12
                        WHEN 'low' THEN 5
                        ELSE 0
                    END
                INTO v_activity_score
                FROM actors a
                WHERE a.id = p_actor_id;

                -- 3. Recent Activity Score (0-15 points)
                SELECT
                    CASE
                        WHEN a.last_activity_date >= CURRENT_DATE - INTERVAL '7 days' THEN 15
                        WHEN a.last_activity_date >= CURRENT_DATE - INTERVAL '30 days' THEN 10
                        WHEN a.last_activity_date >= CURRENT_DATE - INTERVAL '90 days' THEN 5
                        ELSE 0
                    END
                INTO v_recency_score
                FROM actors a
                WHERE a.id = p_actor_id;

                -- 4. Actor Type Score (0-15 points)
                SELECT
                    CASE a.actor_type
                        WHEN 'STATE' THEN 15
                        WHEN 'GOVERNMENT_FORCES' THEN 12
                        WHEN 'INSURGENT' THEN 10
                        WHEN 'MILITIA' THEN 10
                        WHEN 'TERRORIST' THEN 8
                        WHEN 'PMC' THEN 8
                        WHEN 'CARTEL' THEN 8
                        ELSE 5
                    END
                INTO v_type_score
                FROM actors a
                WHERE a.id = p_actor_id;

                -- 5. Global Significance Score (0-10 points)
                SELECT
                    CASE WHEN (a.designations->>'un')::boolean IS TRUE THEN 5 ELSE 0 END +
                    CASE WHEN (a.designations->>'us')::boolean IS TRUE THEN 2 ELSE 0 END +
                    CASE WHEN (a.designations->>'eu')::boolean IS TRUE THEN 2 ELSE 0 END +
                    CASE WHEN (a.designations->>'uk')::boolean IS TRUE THEN 1 ELSE 0 END
                INTO v_significance_score
                FROM actors a
                WHERE a.id = p_actor_id;

                -- Calculate total score
                v_score := v_conflict_score + v_activity_score + v_recency_score + v_type_score + v_significance_score;

                -- Cap at 100
                RETURN LEAST(v_score, 100.00);
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Trigger to auto-update priority_score on actors table
        DB::statement("
            CREATE OR REPLACE FUNCTION update_actor_priority_score_trigger()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.priority_score := calculate_actor_priority_score(NEW.id);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER actor_priority_score_update
            BEFORE INSERT OR UPDATE ON actors
            FOR EACH ROW
            EXECUTE FUNCTION update_actor_priority_score_trigger();
        ");

        // Function to recalculate all actor priority scores (for maintenance)
        DB::statement("
            CREATE OR REPLACE FUNCTION recalculate_all_actor_priorities()
            RETURNS void AS $$
            BEGIN
                UPDATE actors
                SET priority_score = calculate_actor_priority_score(id),
                    updated_at = NOW();
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Trigger to update actor priority when conflict_parties changes
        DB::statement("
            CREATE OR REPLACE FUNCTION update_actor_priority_on_conflict_change()
            RETURNS TRIGGER AS $$
            BEGIN
                -- Update the affected actor's priority score
                IF TG_OP = 'DELETE' THEN
                    UPDATE actors
                    SET priority_score = calculate_actor_priority_score(OLD.actor_id),
                        updated_at = NOW()
                    WHERE id = OLD.actor_id;
                    RETURN OLD;
                ELSE
                    UPDATE actors
                    SET priority_score = calculate_actor_priority_score(NEW.actor_id),
                        updated_at = NOW()
                    WHERE id = NEW.actor_id;
                    RETURN NEW;
                END IF;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            CREATE TRIGGER conflict_parties_priority_update
            AFTER INSERT OR UPDATE OR DELETE ON conflict_parties
            FOR EACH ROW
            EXECUTE FUNCTION update_actor_priority_on_conflict_change();
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS conflict_parties_priority_update ON conflict_parties');
        DB::statement('DROP TRIGGER IF EXISTS actor_priority_score_update ON actors');
        DB::statement('DROP FUNCTION IF EXISTS update_actor_priority_on_conflict_change()');
        DB::statement('DROP FUNCTION IF EXISTS update_actor_priority_score_trigger()');
        DB::statement('DROP FUNCTION IF EXISTS recalculate_all_actor_priorities()');
        DB::statement('DROP FUNCTION IF EXISTS calculate_actor_priority_score(INTEGER)');
    }
};
