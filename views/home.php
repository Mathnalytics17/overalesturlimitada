<main class="home-page">

    <section class="hero-home">
        <div class="hero-overlay"></div>

        <img class="hero-bg" src="/img/home/image (28).png" alt="Paisaje inspirado en Santa Marta, Magdalena">

        <div class="hero-content">
            <div class="hero-text">
                <span class="hero-kicker">Agencia de viajes y turismo</span>
                <h1>Viajes diseñados con confianza, respaldo y atención humana</h1>
                <p>
                    En Alestur te ayudamos a planear cada detalle de tu viaje:
                    Tiquetes, paquetes, servicios complementarios y asesoría
                    personalizada para que vivas una experiencia tranquila y memorable.
                </p>

                <div class="hero-actions">
                    <a href="/packagesTourist" class="btn btn-primary">Ver paquetes</a>
                    <a href="/contact" class="btn btn-secondary">Solicitar asesoría</a>
                </div>

                <div class="hero-badges">
                    <span>Atención personalizada</span>
                    <span>Soporte confiable</span>
                    <span>Experiencias a tu medida</span>
                </div>
            </div>

            <div class="hero-card">
                <img class="hero-logo" src="/img/home/logo.png" alt="Logo Alestur">
                <div class="hero-card-info">
                    <h3>Expertos en viajes</h3>
                    <p>
                        Te acompañamos desde la elección del destino hasta los
                        servicios que necesitas antes, durante y después del viaje.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="home-services-preview">
        <div class="section-heading">
            <span class="section-kicker">Nuestros servicios</span>
            <h2>Todo lo que necesitas para viajar mejor</h2>
            <p>
                Reunimos soluciones clave para que tu experiencia sea más cómoda,
                organizada y segura.
            </p>
        </div>

        <div class="service-grid">
            <article class="service-card">
                <div class="service-icon"><i class="fa-solid fa-plane-departure"></i></div>
                <h3>Tiquetes</h3>
                <p>Opciones de vuelos para distintos destinos con acompañamiento en tu proceso de compra.</p>
                <a href="/tickets">Explorar</a>
            </article>

            <article class="service-card">
                <div class="service-icon"><i class="fa-solid fa-suitcase-rolling"></i></div>
                <h3>Paquetes turísticos</h3>
                <p>Alternativas pensadas para vacaciones, escapadas y experiencias completas.</p>
                <a href="/packagesTourist">Explorar</a>
            </article>

            <article class="service-card">
                <div class="service-icon"><i class="fa-solid fa-passport"></i></div>
                <h3>Servicios extra</h3>
                <p>Visas, asistencias médicas, simcards y otros apoyos para tu viaje.</p>
                <a href="/extra-services">Explorar</a>
            </article>
        </div>
    </section>

    <section class="home-carrousel">
        <?php include_once __DIR__.'/../shared/carrousel.php' ?>
    </section>

    <section class="frequent-questions">
        <div class="section-heading section-heading-center">
            <span class="section-kicker">Preguntas frecuentes</span>
            <h2>Resolvemos las dudas más comunes de nuestros viajeros</h2>
            <p>
                Desde servicios y cambios hasta asesoría y atención personalizada,
                aquí encuentras respuestas rápidas para tomar mejores decisiones.
            </p>
        </div>

        <div class="question-cards-container">

            <article class="question-card">
                <div class="question-icon"><i class="fa-solid fa-handshake"></i></div>
                <h3>Servicios</h3>
                <p>
                    Conoce nuestras soluciones en paquetes turísticos, reservas,
                    orientación de viaje y acompañamiento personalizado.
                </p>
                <a href="/extra-services" class="card-link">Ver más</a>
            </article>

            <article class="question-card">
                <div class="question-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                <h3>Cambios y devoluciones</h3>
                <p>
                    Te orientamos sobre reprogramaciones, condiciones aplicables
                    y procesos relacionados con cambios en tu viaje.
                </p>
                <a href="/pqrs" class="card-link">Ver más</a>
            </article>

            <article class="question-card">
                <div class="question-icon"><i class="fa-solid fa-circle-info"></i></div>
                <h3>Información general</h3>
                <p>
                    Encuentra información útil sobre promociones, novedades,
                    atención al cliente y proceso de compra.
                </p>
                <a href="/aboutUs" class="card-link">Ver más</a>
            </article>

            <article class="question-card featured-card">
                <div class="question-icon"><i class="fa-solid fa-user-tie"></i></div>
                <h3>Asesoría y ventas</h3>
                <p>
                    Nuestro equipo te ayuda a elegir la mejor opción para tu viaje
                    según tu presupuesto, destino y necesidades.
                </p>
                <a href="/contact" class="card-link">Hablar con un asesor</a>
            </article>

        </div>
    </section>

    <?php
    $experiences = $experiences ?? [];

    function experience_asset_url(?string $path, string $fallback = '/img/default-experience.jpg'): string
    {
        $path = trim((string)$path);

        if ($path === '') {
            return $fallback;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);

        return '/' . ltrim($path, '/');
    }
    ?>

    <?php if (!empty($experiences)): ?>
    <section class="travel-experiences">
        <div class="section-heading">
            <span class="section-kicker">Experiencias reales</span>
            <h2>Experiencias de viajeros</h2>
            <p>
                Historias y valoraciones de clientes que confiaron en Alestur para vivir
                viajes memorables, cómodos y bien acompañados.
            </p>
        </div>

        <div class="experiences-grid">
            <?php foreach ($experiences as $exp): ?>
                <?php
                    $coverPath = experience_asset_url($exp->cover_image->image_path ?? null);
                    $excerpt = mb_strimwidth((string)($exp->story ?? ''), 0, 180, '...');
                ?>
                <article class="experience-card">
                    <img
                        class="experience-image"
                        src="<?= htmlspecialchars($coverPath) ?>"
                        alt="<?= htmlspecialchars($exp->title ?? '') ?>"
                    >

                    <div class="experience-body">
                        <div class="experience-rating">
                            <?= str_repeat('★', max(1, min(5, (int)($exp->rating ?? 5)))) ?>
                        </div>

                        <div class="experience-title">
                            <?= htmlspecialchars($exp->title ?? '') ?>
                        </div>

                        <div class="experience-text">
                            <?= nl2br(htmlspecialchars($excerpt)) ?>
                        </div>

                        <div class="experience-author">
                            <?= htmlspecialchars($exp->display_name ?: $exp->customer_name ?: 'Cliente') ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="experiences-actions">
            <a href="/experiences" class="btn btn-primary">Ver más experiencias</a>
        </div>
    </section>
    <?php endif; ?>

    <section class="home-cta">
        <div class="home-cta-box">
            <div>
                <span class="section-kicker section-kicker-light">Empieza hoy</span>
                <h2>Planea tu próximo viaje con respaldo profesional</h2>
                <p>
                    Estamos listos para ayudarte a elegir el servicio ideal y acompañarte en cada paso.
                </p>
            </div>

            <div class="home-cta-actions">
                <a href="/contact" class="btn btn-light">Contáctanos</a>
                <a href="/packagesTourist" class="btn btn-outline-light">Ver destinos</a>
            </div>
        </div>
    </section>

</main>
