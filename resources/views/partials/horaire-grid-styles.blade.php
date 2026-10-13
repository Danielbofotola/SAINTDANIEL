{{-- Styles de la grille horaire hebdomadaire (grille, boîtes de cours, pause, légende). --}}
<style>
    .horaire-entity {
        background: #f2f5f8;
        border: 1px solid #b0c2d0;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
        padding: 14px;
        margin-bottom: 16px;
    }
    .horaire-entity .horaire-entity-title {
        font-size: .95rem;
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    .table-schedule { font-size: .8rem; background: #fff; border-radius: 6px; table-layout: fixed; }
    .table-schedule th { background: #cbd5e1 !important; color: #0f172a; font-weight: 700; text-align: center; vertical-align: middle; padding: 10px 4px; }
    .table-schedule td { height: 85px; vertical-align: top; padding: 4px; background: #f8fafc; }
    .table-schedule .time-col { background: #e2e8f0 !important; font-weight: 700; color: #334155; text-align: center; vertical-align: middle !important; width: 90px; font-size: .75rem; }
    .table-schedule .today-header { background: #0284c7 !important; color: #fff !important; }
    .course-box { border-radius: 5px; padding: 6px 8px; margin-bottom: 4px; height: 100%; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 1px 3px rgba(0, 0, 0, .08); border-left: 4px solid #0284c7; }
    .course-blue { background: #e0f2fe; border-left-color: #0284c7; color: #0369a1; }
    .course-amber { background: #fef3c7; border-left-color: #d97706; color: #92400e; }
    .course-green { background: #dcfce7; border-left-color: #16a34a; color: #15803d; }
    .course-purple { background: #f3e8ff; border-left-color: #9333ea; color: #6b21a8; }
    .course-title { font-weight: 700; font-size: .78rem; line-height: 1.1; margin-bottom: 2px; }
    .course-meta { font-size: .7rem; display: flex; justify-content: space-between; align-items: center; font-weight: 600; gap: 4px; flex-wrap: wrap; }
    .pause-row td { background: #f1f5f9 !important; font-weight: 700; color: #475569; text-align: center; vertical-align: middle !important; height: auto !important; }
    .couleur-point { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 4px; vertical-align: middle; }
    .couleur-point.course-blue { background: #0284c7; }
    .couleur-point.course-amber { background: #d97706; }
    .couleur-point.course-green { background: #16a34a; }
    .couleur-point.course-purple { background: #9333ea; }
    @media print { .horaire-entity { box-shadow: none; } .table-schedule td { height: 70px; } }
</style>
