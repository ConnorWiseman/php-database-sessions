<!DOCTYPE html>
<html lang="en-US">
    <head>
        <title>{{page-title}}</title>
        <meta charset="utf-8" />
        <meta auth-key="{{auth-key}}" />
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script src="/scripts/main.js"></script>
        <link rel="stylesheet" href="/styles/reset.css" />
        <link rel="stylesheet" href="/styles/style.css" />
    </head>

    <body>
        <!-- Header -->
        <header id="page-header" role="banner">
            <h1>{{page-title}}</h1>

            <div class="wrapper">
                <!-- User links -->
                <div id="user-links" role="navigation">
                    <ul>
                        {{user-links}}
                    </ul>
                </div>
                <!-- User links -->

                <!-- Navigation links -->
                <div id="nav-links" role="navigation">
                    <ul>
                        <li><a href="/">Home</a></li>
                    </ul>
                </div>
                <!-- Navigation links -->
            </div>
        </header>
        <!-- Header -->

        <!-- Content -->
        <main class="wrapper" role="main">
            <section>
                <h2>{{section-title}}</h2>

                {{section-contents}}
            </section>
        </main>
        <!-- Content -->
    </body>
</html>