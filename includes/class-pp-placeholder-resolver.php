<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class PP_Placeholder_Resolver {

	public static function get_all_placeholder_keys(): array {
		return [
			'KUENSTLERNAME','PROGRAMMNAME','SHOWDAUER','SPRACHE_DER_SHOW','PROGRAMMKONTEXT',
			'KUENSTLERPERSOENLICHKEIT','SPRACHSTIL','ENERGIELEVEL','PUBLIKUMSNAEHE',
			'CHARAKTERZUEGE','NO_GO_KUENSTLERWIRKUNG','REFERENZEN',
			'ZIELPUBLIKUM','ALTERSSTRUKTUR','INTERAKTIONSGRAD',
			'TONLAGE_GGUE_PUBLIKUM','PUBLIKUMSBESONDERHEITEN',
			'HUMORTYP','GEWICHTUNG_HUMOR_TIEFE','ROTER_FADEN',
			'ZENTRALE_THEMEN','EMOTIONALE_KURVE','GESAMTWIRKUNG','NO_GO_INHALTE',
			'FORM_DER_ZWISCHENTEILE','MODERATIONSART','SKETCHART',
			'RUNNING_GAGS','RUNNING_GAG_DETAILS',
			'PUBLIKUMSINTERAKTION','PUBLIKUMSINTERAKTION_DETAILS',
			'VORHANDENES_MATERIAL_JA_NEIN','UMGANG_MIT_VORHANDENEM_MATERIAL','VORHANDENE_MODERATIONEN',
			'SONGLISTE','SONG_NOTIZEN',
			'POSITIONIERUNG_DER_ZWISCHENTEILE','FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN','TIMING_VORGABEN',
		];
	}

	public static function get_defaults(): array {
		return [
			'KUENSTLERNAME'                      => '',
			'PROGRAMMNAME'                       => '',
			'SHOWDAUER'                          => '120 Minuten',
			'SPRACHE_DER_SHOW'                   => 'Deutsch',
			'PROGRAMMKONTEXT'                    => 'Keine zusätzlichen Kontextangaben.',
			'KUENSTLERPERSOENLICHKEIT'           => '',
			'SPRACHSTIL'                         => '',
			'ENERGIELEVEL'                       => '',
			'PUBLIKUMSNAEHE'                     => '',
			'CHARAKTERZUEGE'                     => 'Keine weiteren Angaben.',
			'NO_GO_KUENSTLERWIRKUNG'             => 'Keine zusätzlichen No-Go-Angaben.',
			'REFERENZEN'                         => 'Keine Referenzen angegeben.',
			'ZIELPUBLIKUM'                       => 'Keine genauere Zielpublikumsangabe vorhanden.',
			'ALTERSSTRUKTUR'                     => 'Nicht näher definiert.',
			'INTERAKTIONSGRAD'                   => 'gezielte einzelne Interaktionen',
			'TONLAGE_GGUE_PUBLIKUM'              => 'locker und respektvoll',
			'PUBLIKUMSBESONDERHEITEN'            => 'Keine besonderen Hinweise.',
			'HUMORTYP'                           => '',
			'GEWICHTUNG_HUMOR_TIEFE'             => '',
			'ROTER_FADEN'                        => '',
			'ZENTRALE_THEMEN'                    => 'Keine zusätzlichen Themenvorgaben.',
			'EMOTIONALE_KURVE'                   => 'Dramaturgisch ausgewogene Entwicklung mit Steigerung zum Schluss.',
			'GESAMTWIRKUNG'                      => 'Die Show soll stimmig, unterhaltsam, glaubwürdig und künstlerisch passend wirken.',
			'NO_GO_INHALTE'                      => 'Keine zusätzlichen No-Go-Inhalte definiert.',
			'FORM_DER_ZWISCHENTEILE'             => '',
			'MODERATIONSART'                     => 'natürliche, bühnentaugliche Moderationen',
			'SKETCHART'                          => 'situativ passende kurze Zwischenteile',
			'RUNNING_GAGS'                       => 'Nein',
			'RUNNING_GAG_DETAILS'                => 'Keine Running-Gag-Vorgabe.',
			'PUBLIKUMSINTERAKTION'               => 'Nein',
			'PUBLIKUMSINTERAKTION_DETAILS'       => 'Keine besondere Publikumsinteraktion vorgegeben.',
			'VORHANDENES_MATERIAL_JA_NEIN'       => 'Nein',
			'UMGANG_MIT_VORHANDENEM_MATERIAL'    => 'nur als Inspirationsbasis nutzen',
			'VORHANDENE_MODERATIONEN'            => 'Keine vorhandenen Moderationstexte hinterlegt.',
			'SONGLISTE'                          => 'Keine Songliste hinterlegt.',
			'SONG_NOTIZEN'                       => 'Keine zusätzlichen Songhinweise vorhanden.',
			'POSITIONIERUNG_DER_ZWISCHENTEILE'   => 'Keine festen Positionierungen vorgegeben.',
			'FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN'=> 'Keine zusätzlichen Funktionshinweise.',
			'TIMING_VORGABEN'                    => 'Keine konkreten Timing-Vorgaben.',
		];
	}

	/**
	 * Resolves all placeholders directly from the raw POST data array $d —
	 * no DB read, no get_post_meta() calls. Used during save to avoid any
	 * cache-invalidation race conditions caused by wp_update_post().
	 *
	 * @param array $d  The decoded form data array from the frontend (same structure
	 *                  as used by write_project_data()).
	 */
	public function resolve_from_data( array $d ): array {
		// Alle Keys normalisieren → Kleinbuchstaben. Schützt gegen Groß/Klein-Fehler
		// egal ob das Frontend name="kuenstlername" oder name="KUENSTLERNAME" sendet.
		$d        = array_change_key_case( $d, CASE_LOWER );
		$defaults = self::get_defaults();
		$data     = [];

		$simple = [
			'KUENSTLERNAME'                  => 'kuenstlername',
			'PROGRAMMNAME'                   => 'programmname',
			'SHOWDAUER'                      => 'showdauer',
			'SPRACHE_DER_SHOW'               => 'sprache_der_show',
			'PROGRAMMKONTEXT'                => 'programmkontext',
			'ENERGIELEVEL'                   => 'energielevel',
			'PUBLIKUMSNAEHE'                 => 'publikumsnaehe',
			'CHARAKTERZUEGE'                 => 'charakterzuege',
			'NO_GO_KUENSTLERWIRKUNG'         => 'no_go_kuenstlerwirkung',
			'REFERENZEN'                     => 'referenzen',
			'ZIELPUBLIKUM'                   => 'zielpublikum',
			'ALTERSSTRUKTUR'                 => 'altersstruktur',
			'INTERAKTIONSGRAD'               => 'interaktionsgrad',
			'PUBLIKUMSBESONDERHEITEN'        => 'publikumsbesonderheiten',
			'ROTER_FADEN'                    => 'roter_faden',
			'ZENTRALE_THEMEN'                => 'zentrale_themen',
			'EMOTIONALE_KURVE'               => 'emotionale_kurve',
			'GESAMTWIRKUNG'                  => 'gesamtwirkung',
			'NO_GO_INHALTE'                  => 'no_go_inhalte',
			'RUNNING_GAGS'                   => 'running_gags',
			'RUNNING_GAG_DETAILS'            => 'running_gag_details',
			'PUBLIKUMSINTERAKTION'           => 'publikumsinteraktion',
			'PUBLIKUMSINTERAKTION_DETAILS'   => 'publikumsinteraktion_details',
			'VORHANDENES_MATERIAL_JA_NEIN'   => 'vorhandenes_material_ja_nein',
			'UMGANG_MIT_VORHANDENEM_MATERIAL'=> 'umgang_mit_vorhandenem_material',
			'VORHANDENE_MODERATIONEN'        => 'vorhandene_moderationen',
		];
		foreach ( $simple as $ph => $dk ) {
			$v = (string) ( $d[ $dk ] ?? '' );
			$data[ $ph ] = ! empty( trim( $v ) ) ? $v : ( $defaults[ $ph ] ?? '' );
		}

		// Kombinationsfelder
		$data['SPRACHSTIL']             = $this->combo_from_data( $d, 'sprachstil',             'sprachstil_custom',             $defaults['SPRACHSTIL'] );
		$data['TONLAGE_GGUE_PUBLIKUM']  = $this->combo_from_data( $d, 'tonlage_ggue_publikum',  'tonlage_custom',                $defaults['TONLAGE_GGUE_PUBLIKUM'] );
		$data['GEWICHTUNG_HUMOR_TIEFE'] = $this->combo_from_data( $d, 'gewichtung_humor_tiefe', 'gewichtung_custom',             $defaults['GEWICHTUNG_HUMOR_TIEFE'] );

		// Mehrfachauswahl
		$data['KUENSTLERPERSOENLICHKEIT'] = $this->multi_from_data( $d, 'kuenstlerpersoenlichkeit', 'kuenstlerpersoenlichkeit_custom', $defaults['KUENSTLERPERSOENLICHKEIT'] );
		$data['HUMORTYP']                 = $this->multi_from_data( $d, 'humortyp',                 'humortyp_custom',                $defaults['HUMORTYP'] );
		$data['FORM_DER_ZWISCHENTEILE']   = $this->multi_from_data( $d, 'form_der_zwischenteile',   'form_der_zwischenteile_custom',  $defaults['FORM_DER_ZWISCHENTEILE'] );
		$data['MODERATIONSART']           = $this->multi_from_data( $d, 'moderationsart',           'moderationsart_custom',          $defaults['MODERATIONSART'] );
		$data['SKETCHART']                = $this->multi_from_data( $d, 'sketchart',                'sketchart_custom',               $defaults['SKETCHART'] );

		// Songs
		$songs = isset( $d['songs'] ) && is_array( $d['songs'] ) ? $d['songs'] : [];
		if ( empty( $songs ) ) {
			$data['SONGLISTE']    = $defaults['SONGLISTE'];
			$data['SONG_NOTIZEN'] = $defaults['SONG_NOTIZEN'];
		} else {
			$data['SONGLISTE']    = $this->song_list( $songs );
			$data['SONG_NOTIZEN'] = $this->song_notes( $songs );
		}

		// Zwischenteile
		$zt = isset( $d['zwischenteile'] ) && is_array( $d['zwischenteile'] ) ? $d['zwischenteile'] : [];
		if ( empty( $zt ) ) {
			$data['POSITIONIERUNG_DER_ZWISCHENTEILE']    = $defaults['POSITIONIERUNG_DER_ZWISCHENTEILE'];
			$data['FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN'] = $defaults['FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN'];
			$data['TIMING_VORGABEN']                     = $defaults['TIMING_VORGABEN'];
		} else {
			$data['POSITIONIERUNG_DER_ZWISCHENTEILE']    = $this->zt_positions( $zt );
			$data['FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN'] = $this->zt_functions( $zt );
			$data['TIMING_VORGABEN']                     = $this->zt_timing( $zt );
		}

		return $data;
	}

	public function resolve( int $post_id ): array {
		$defaults = self::get_defaults();
		$data     = [];

		$simple = [
			'KUENSTLERNAME'                  => '_pp_kuenstlername',
			'PROGRAMMNAME'                   => '_pp_programmname',
			'SHOWDAUER'                      => '_pp_showdauer',
			'SPRACHE_DER_SHOW'               => '_pp_sprache_der_show',
			'PROGRAMMKONTEXT'                => '_pp_programmkontext',
			'ENERGIELEVEL'                   => '_pp_energielevel',
			'PUBLIKUMSNAEHE'                 => '_pp_publikumsnaehe',
			'CHARAKTERZUEGE'                 => '_pp_charakterzuege',
			'NO_GO_KUENSTLERWIRKUNG'         => '_pp_no_go_kuenstlerwirkung',
			'REFERENZEN'                     => '_pp_referenzen',
			'ZIELPUBLIKUM'                   => '_pp_zielpublikum',
			'ALTERSSTRUKTUR'                 => '_pp_altersstruktur',
			'INTERAKTIONSGRAD'               => '_pp_interaktionsgrad',
			'PUBLIKUMSBESONDERHEITEN'        => '_pp_publikumsbesonderheiten',
			'ROTER_FADEN'                    => '_pp_roter_faden',
			'ZENTRALE_THEMEN'                => '_pp_zentrale_themen',
			'EMOTIONALE_KURVE'               => '_pp_emotionale_kurve',
			'GESAMTWIRKUNG'                  => '_pp_gesamtwirkung',
			'NO_GO_INHALTE'                  => '_pp_no_go_inhalte',
			'RUNNING_GAGS'                   => '_pp_running_gags',
			'RUNNING_GAG_DETAILS'            => '_pp_running_gag_details',
			'PUBLIKUMSINTERAKTION'           => '_pp_publikumsinteraktion',
			'PUBLIKUMSINTERAKTION_DETAILS'   => '_pp_publikumsinteraktion_details',
			'VORHANDENES_MATERIAL_JA_NEIN'   => '_pp_vorhandenes_material_ja_nein',
			'UMGANG_MIT_VORHANDENEM_MATERIAL'=> '_pp_umgang_mit_vorhandenem_material',
			'VORHANDENE_MODERATIONEN'        => '_pp_vorhandene_moderationen',
		];
		foreach ( $simple as $ph => $mk ) {
			$v = (string) get_post_meta( $post_id, $mk, true );
			$data[ $ph ] = ! empty( trim( $v ) ) ? $v : ( $defaults[ $ph ] ?? '' );
		}

		// Kombinationsfelder
		$data['SPRACHSTIL']             = $this->combo( $post_id, '_pp_sprachstil', '_pp_sprachstil_custom', $defaults['SPRACHSTIL'] );
		$data['TONLAGE_GGUE_PUBLIKUM']  = $this->combo( $post_id, '_pp_tonlage_ggue_publikum', '_pp_tonlage_custom', $defaults['TONLAGE_GGUE_PUBLIKUM'] );
		$data['GEWICHTUNG_HUMOR_TIEFE'] = $this->combo( $post_id, '_pp_gewichtung_humor_tiefe', '_pp_gewichtung_custom', $defaults['GEWICHTUNG_HUMOR_TIEFE'] );

		// Mehrfachauswahl
		$data['KUENSTLERPERSOENLICHKEIT'] = $this->multi( $post_id, '_pp_kuenstlerpersoenlichkeit', '_pp_kuenstlerpersoenlichkeit_custom', $defaults['KUENSTLERPERSOENLICHKEIT'] );
		$data['HUMORTYP']                 = $this->multi( $post_id, '_pp_humortyp', '_pp_humortyp_custom', $defaults['HUMORTYP'] );
		$data['FORM_DER_ZWISCHENTEILE']   = $this->multi( $post_id, '_pp_form_der_zwischenteile', '_pp_form_der_zwischenteile_custom', $defaults['FORM_DER_ZWISCHENTEILE'] );
		$data['MODERATIONSART']           = $this->multi( $post_id, '_pp_moderationsart', '_pp_moderationsart_custom', $defaults['MODERATIONSART'] );
		$data['SKETCHART']                = $this->multi( $post_id, '_pp_sketchart', '_pp_sketchart_custom', $defaults['SKETCHART'] );

		// Songs
		$songs = get_post_meta( $post_id, '_pp_songs', true );
		if ( ! is_array( $songs ) || empty( $songs ) ) {
			$data['SONGLISTE']    = $defaults['SONGLISTE'];
			$data['SONG_NOTIZEN'] = $defaults['SONG_NOTIZEN'];
		} else {
			$data['SONGLISTE']    = $this->song_list( $songs );
			$data['SONG_NOTIZEN'] = $this->song_notes( $songs );
		}

		// Zwischenteile
		$zt = get_post_meta( $post_id, '_pp_zwischenteile', true );
		if ( ! is_array( $zt ) || empty( $zt ) ) {
			$data['POSITIONIERUNG_DER_ZWISCHENTEILE']    = $defaults['POSITIONIERUNG_DER_ZWISCHENTEILE'];
			$data['FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN'] = $defaults['FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN'];
			$data['TIMING_VORGABEN']                     = $defaults['TIMING_VORGABEN'];
		} else {
			$data['POSITIONIERUNG_DER_ZWISCHENTEILE']    = $this->zt_positions( $zt );
			$data['FUNKTIONSHINWEISE_ZU_ZWISCHENTEILEN'] = $this->zt_functions( $zt );
			$data['TIMING_VORGABEN']                     = $this->zt_timing( $zt );
		}
		return $data;
	}

	private function combo_from_data( array $d, string $key, string $custom_key, string $default ): string {
		$v = (string) ( $d[ $key ] ?? '' );
		$c = (string) ( $d[ $custom_key ] ?? '' );
		if ( ! empty( trim( $c ) ) ) $v .= ( ! empty( $v ) ? '; zusätzlich: ' : '' ) . $c;
		return ! empty( trim( $v ) ) ? $v : $default;
	}

	private function multi_from_data( array $d, string $key, string $custom_key, string $default ): string {
		$arr = isset( $d[ $key ] ) && is_array( $d[ $key ] ) ? $d[ $key ] : [];
		$c   = (string) ( $d[ $custom_key ] ?? '' );
		if ( empty( $arr ) ) {
			return ! empty( trim( $c ) ) ? $c : $default;
		}
		$r = implode( ', ', $arr );
		if ( ! empty( trim( $c ) ) ) $r .= '; zusätzlich: ' . $c;
		return $r;
	}

	private function combo( int $id, string $key, string $custom_key, string $default ): string {
		$v = (string) get_post_meta( $id, $key, true );
		$c = (string) get_post_meta( $id, $custom_key, true );
		if ( ! empty( trim( $c ) ) ) $v .= ( ! empty( $v ) ? '; zusätzlich: ' : '' ) . $c;
		return ! empty( trim( $v ) ) ? $v : $default;
	}

	private function multi( int $id, string $key, string $custom_key, string $default ): string {
		$arr = get_post_meta( $id, $key, true );
		$c   = (string) get_post_meta( $id, $custom_key, true );
		if ( ! is_array( $arr ) || empty( $arr ) ) {
			return ! empty( trim( $c ) ) ? $c : $default;
		}
		$r = implode( ', ', $arr );
		if ( ! empty( trim( $c ) ) ) $r .= '; zusätzlich: ' . $c;
		return $r;
	}

	private function song_list( array $songs ): string {
		$lines = [];
		foreach ( $songs as $i => $s ) {
			if ( empty( $s['title'] ) ) continue;
			$lines[] = ( $i + 1 ) . '. ' . $s['title'];
		}
		return $lines ? implode( "\n", $lines ) : 'Keine Songliste hinterlegt.';
	}

	private function song_notes( array $songs ): string {
		$lines = []; $any = false;
		foreach ( $songs as $i => $s ) {
			if ( empty( $s['title'] ) ) continue;
			$parts = [];
			if ( ! empty( $s['mood'] ) ) { $parts[] = 'Stimmung: ' . $s['mood']; $any = true; }
			if ( ! empty( $s['note'] ) ) { $parts[] = $s['note']; $any = true; }
			$lines[] = ( $i + 1 ) . '. ' . $s['title'] . ': ' . ( $parts ? implode( ' | ', $parts ) : 'keine Zusatznotiz' );
		}
		return ( ! $any || ! $lines ) ? 'Keine zusätzlichen Songhinweise vorhanden.' : implode( "\n", $lines );
	}

	private function zt_positions( array $zt ): string {
		$lines = [];
		foreach ( $zt as $i => $z ) {
			if ( empty( $z['position'] ) ) continue;
			$l = ( $i + 1 ) . '.';
			if ( ! empty( $z['title'] ) ) $l .= ' „' . $z['title'] . '" –';
			$l .= ' ' . $z['position'];
			$lines[] = $l;
		}
		return $lines ? implode( "\n", $lines ) : 'Keine festen Positionierungen vorgegeben.';
	}

	private function zt_functions( array $zt ): string {
		$lines = [];
		foreach ( $zt as $i => $z ) {
			if ( empty( $z['position'] ) ) continue;
			$l = ( $i + 1 ) . '. ' . $z['position'];
			$f = trim( ( $z['funktion'] ?? '' ) . ' ' . ( $z['funktion_custom'] ?? '' ) );
			if ( ! empty( $f ) ) $l .= ' → ' . $f;
			if ( ! empty( $z['hinweis'] ) ) $l .= "\n   Hinweis: " . $z['hinweis'];
			if ( ! empty( $z['text'] ) )    $l .= "\n   Vorhandener Text:\n   " . str_replace( "\n", "\n   ", $z['text'] );
			$lines[] = $l;
		}
		return $lines ? implode( "\n\n", $lines ) : 'Keine zusätzlichen Funktionshinweise.';
	}

	private function zt_timing( array $zt ): string {
		$lines = []; $any = false;
		foreach ( $zt as $i => $z ) {
			if ( empty( $z['position'] ) || empty( $z['timing'] ) ) continue;
			$lines[] = ( $i + 1 ) . '. ' . $z['position'] . ': ' . $z['timing'];
			$any = true;
		}
		return $any ? implode( "\n", $lines ) : 'Keine konkreten Timing-Vorgaben.';
	}
}
