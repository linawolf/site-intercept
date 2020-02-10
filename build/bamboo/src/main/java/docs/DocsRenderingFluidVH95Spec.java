package docs;

import com.atlassian.bamboo.specs.api.BambooSpec;
import com.atlassian.bamboo.specs.api.builders.BambooKey;
import com.atlassian.bamboo.specs.api.builders.BambooOid;
import com.atlassian.bamboo.specs.api.builders.Variable;
import com.atlassian.bamboo.specs.api.builders.permission.PermissionType;
import com.atlassian.bamboo.specs.api.builders.permission.Permissions;
import com.atlassian.bamboo.specs.api.builders.permission.PlanPermissions;
import com.atlassian.bamboo.specs.api.builders.plan.Job;
import com.atlassian.bamboo.specs.api.builders.plan.Plan;
import com.atlassian.bamboo.specs.api.builders.plan.PlanIdentifier;
import com.atlassian.bamboo.specs.api.builders.plan.Stage;
import com.atlassian.bamboo.specs.api.builders.plan.artifact.Artifact;
import com.atlassian.bamboo.specs.api.builders.plan.artifact.ArtifactSubscription;
import com.atlassian.bamboo.specs.api.builders.plan.branches.BranchCleanup;
import com.atlassian.bamboo.specs.api.builders.plan.branches.PlanBranchManagement;
import com.atlassian.bamboo.specs.api.builders.plan.configuration.AllOtherPluginsConfiguration;
import com.atlassian.bamboo.specs.api.builders.plan.configuration.ConcurrentBuilds;
import com.atlassian.bamboo.specs.api.builders.project.Project;
import com.atlassian.bamboo.specs.api.builders.requirement.Requirement;
import com.atlassian.bamboo.specs.builders.task.ArtifactItem;
import com.atlassian.bamboo.specs.builders.task.CommandTask;
import com.atlassian.bamboo.specs.builders.task.ScpTask;
import com.atlassian.bamboo.specs.builders.task.ScriptTask;
import com.atlassian.bamboo.specs.builders.task.SshTask;
import com.atlassian.bamboo.specs.builders.trigger.ScheduledTrigger;
import com.atlassian.bamboo.specs.util.BambooServer;
import com.atlassian.bamboo.specs.util.MapBuilder;

@BambooSpec
public class DocsRenderingFluidVH95Spec {

    public Plan plan() {
        final Plan plan = new Plan(new Project()
                .oid(new BambooOid("18cp87dwzw7b6"))
                .key(new BambooKey("CORE"))
                .name("Core")
                .description("TYPO3 Core Builds"),
            "Docs - Rendering Fluid VH 9.5",
            new BambooKey("DRF"))
            .oid(new BambooOid("18cfizsjs2xvl"))
            .pluginConfigurations(new ConcurrentBuilds(),
                new AllOtherPluginsConfiguration()
                    .configuration(new MapBuilder()
                            .put("custom.buildExpiryConfig", new MapBuilder()
                                .put("duration", "1")
                                .put("period", "days")
                                .put("labelsToKeep", "")
                                .put("buildsToKeep", "")
                                .put("enabled", "true")
                                .put("expiryTypeArtifact", "true")
                                .build())
                            .build()))
            .stages(new Stage("Render Stage")
                    .jobs(new Job("Render",
                            new BambooKey("JOB1"))
                            .pluginConfigurations(new AllOtherPluginsConfiguration()
                                    .configuration(new MapBuilder()
                                            .put("custom", new MapBuilder()
                                                .put("auto", new MapBuilder()
                                                    .put("regex", "")
                                                    .put("label", "")
                                                    .build())
                                                .put("buildHangingConfig.enabled", "false")
                                                .put("ncover.path", "")
                                                .put("clover", new MapBuilder()
                                                    .put("path", "")
                                                    .put("license", "")
                                                    .put("useLocalLicenseKey", "true")
                                                    .build())
                                                .build())
                                            .build()))
                            .artifacts(new Artifact()
                                    .name("docs.tgz")
                                    .copyPattern("docs.tgz")
                                    .shared(true))
                            .tasks(new ScriptTask()
                                    .description("Checkout core repository")
                                    .inlineBody("#!/bin/bash\n\nset -x\n\nfunction fixPermissions() {\n    docker run \\\n        -v /bamboo-data/${BAMBOO_COMPOSE_PROJECT_NAME}/${bamboo_buildKey}/project:/PROJECT \\\n        -v /bamboo-data/${BAMBOO_COMPOSE_PROJECT_NAME}/${bamboo_buildKey}/result:/RESULT \\\n        -e HOME=${HOME} \\\n        --name ${BAMBOO_COMPOSE_PROJECT_NAME}sib_adhoc \\\n        --rm \\\n        --entrypoint bash \\\n        typo3gmbh/php72:3.0 \\\n        -c \"set -x; chown -R ${HOST_UID} /PROJECT /RESULT\"\n}\n\nfixPermissions\n\nrm -rf project result\nmkdir -p project result\ngit clone https://github.com/TYPO3/TYPO3.CMS.git project\ncd project && git checkout ${bamboo_VERSION_NUMBER}"),
                                new ScriptTask()
                                    .description("Generate core xsd schema files")
                                    .inlineBody("#!/bin/bash\n\nfunction generateSchemas() {\n    docker run \\\n        -v /bamboo-data/${BAMBOO_COMPOSE_PROJECT_NAME}/${bamboo_buildKey}/project:/PROJECT \\\n        -v /bamboo-data/${BAMBOO_COMPOSE_PROJECT_NAME}/${bamboo_buildKey}/result:/RESULT \\\n        -e HOME=${HOME} \\\n        --name ${BAMBOO_COMPOSE_PROJECT_NAME}sib_adhoc \\\n        --rm \\\n        --entrypoint bash \\\n        typo3gmbh/php72:3.0 \\\n        -c \"set -x; cd /PROJECT && composer require -o -n --no-progress typo3/fluid-schema-generator \\\"^2.1\\\" && mkdir -p /RESULT/schemas/typo3fluid/fluid/latest && ./bin/generateschema TYPO3Fluid\\\\\\Fluid > /RESULT/schemas/typo3fluid/fluid/latest/schema.xsd && mkdir -p /RESULT/schemas/typo3/core/latest && ./bin/generateschema TYPO3\\\\\\CMS\\\\\\Core > /RESULT/schemas/typo3/core/latest/schema.xsd && mkdir -p /RESULT/schemas/typo3/fluid/latest && ./bin/generateschema TYPO3\\\\\\CMS\\\\\\Fluid > /RESULT/schemas/typo3/fluid/latest/schema.xsd && mkdir -p /RESULT/schemas/typo3/backend/latest && ./bin/generateschema TYPO3\\\\\\CMS\\\\\\Backend > /RESULT/schemas/typo3/backend/latest/schema.xsd  ; chown -R ${HOST_UID} /PROJECT /RESULT\"\n}\n\ngenerateSchemas"),
                                new ScriptTask()
                                    .description("Generate RST from schema.xsd files")
                                    .inlineBody("#!/bin/bash\n\nset -x\n\nchown -R $(id -u):$(id -g) project result\nrm -rf project\nmkdir -p project\ngit clone --depth 1 --single-branch --branch master https://github.com/maddy2101/fluid-documentation-generator.git project || exit 1\n\nfunction generateRst() {\n    docker run \\\n        -v /bamboo-data/${BAMBOO_COMPOSE_PROJECT_NAME}/${bamboo_buildKey}/project:/PROJECT \\\n        -v /bamboo-data/${BAMBOO_COMPOSE_PROJECT_NAME}/${bamboo_buildKey}/result:/RESULT \\\n        -e HOME=${HOME} \\\n        --name ${BAMBOO_COMPOSE_PROJECT_NAME}sib_adhoc \\\n        --rm \\\n        --entrypoint bash \\\n        typo3gmbh/php72:3.0 \\\n        -c \"set -x; cd /PROJECT && composer install --no-dev -n -o --no-progress && cp -r /RESULT/schemas/* schemas/ && ./bin/generate-fluid-documentation && mkdir -p /RESULT/documentation && cp -r public/* /RESULT/documentation/ ; chown -R ${HOST_UID} /PROJECT /RESULT\"\n}\n\ngenerateRst\n\nfind result/documentation -type f -name '*.xsd' -delete\nfind result/documentation -type f -name '*.json' -delete\nfind result/documentation -type f -name '*.html' -delete"),
                                new ScriptTask()
                                    .description("Render Documentation")
                                    .inlineBody("#!/bin/bash\n\nset -x\n\nchown -R $(id -u):$(id -g) project result\nrm -rf project\nmkdir -p project\ngit clone --depth 1 https://github.com/TYPO3-Documentation/TYPO3CMS-Reference-ViewHelper.git project || exit 1\n\nrm -rf project/Documentation/typo3 project/Documentation/typo3fluid\ncp -r result/documentation/* project/Documentation/\n\nsed -i \"s/version.*=.*/version = ${bamboo_VERSION_NUMBER}/g\" project/Documentation/Settings.cfg\nsed -i \"s/release.*=.*/release = ${bamboo_VERSION_NUMBER}/g\" project/Documentation/Settings.cfg\nsed -i \"s/github_branch.*=.*/github_branch = ${bamboo_VERSION_NUMBER}/g\" project/Documentation/Settings.cfg\n\ntouch project/jobfile.json\ncat << EOF > project/jobfile.json\n{\n    \"Overrides_cfg\": {\n        \"html_theme_options\": {\n            \"docstypo3org\": \"yes\",\n            \"add_piwik\": \"yes\",\n            \"show_legalinfo\": \"yes\"\n        }\n    }\n}\nEOF\n\nfunction renderDocs() {\n    docker run \\\n        -v /bamboo-data/${BAMBOO_COMPOSE_PROJECT_NAME}/${bamboo_buildKey}/project:/PROJECT \\\n        -v /bamboo-data/${BAMBOO_COMPOSE_PROJECT_NAME}/${bamboo_buildKey}/RenderedDocumentation/:/RESULT \\\n        --name ${BAMBOO_COMPOSE_PROJECT_NAME}sib_adhoc \\\n        --rm \\\n        --entrypoint bash \\\n        t3docs/render-documentation:v2.4.0 \\\n        -c \"/ALL/Menu/mainmenu.sh makehtml -c replace_static_in_html 1 -c make_singlehtml 1 -c jobfile /PROJECT/jobfile.json; chown ${HOST_UID} -R /PROJECT /RESULT\"\n}\nmkdir -p RenderedDocumentation\nmkdir -p FinalDocumentation\nrenderDocs\n(shopt -s dotglob; mv RenderedDocumentation/Result/project/0.0.0/* FinalDocumentation)\nrm -rf RenderedDocumentation project result"),
                                new CommandTask()
                                    .description("archive result")
                                    .executable("tar")
                                    .argument("cfz docs.tgz FinalDocumentation"))
                            .requirements(new Requirement("system.hasDocker")
                                    .matchValue("1.0")
                                    .matchType(Requirement.MatchType.EQUALS))
                            .cleanWorkingDirectory(true)),
                new Stage("Deploy Stage")
                    .jobs(new Job("Deploy",
                            new BambooKey("DEP"))
                            .pluginConfigurations(new AllOtherPluginsConfiguration()
                                    .configuration(new MapBuilder()
                                            .put("custom", new MapBuilder()
                                                .put("auto", new MapBuilder()
                                                    .put("regex", "")
                                                    .put("label", "")
                                                    .build())
                                                .put("buildHangingConfig.enabled", "false")
                                                .put("ncover.path", "")
                                                .put("clover", new MapBuilder()
                                                    .put("path", "")
                                                    .put("license", "")
                                                    .put("useLocalLicenseKey", "true")
                                                    .build())
                                                .build())
                                            .build()))
                            .tasks(new SshTask().authenticateWithKeyWithPassphrase("BAMSCRT@0@0@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", "BAMSCRT@0@0@oUXHLLxIhG+krd5vDgsSWg==")
                                    .description("mkdir")
                                    .host("srv007.typo3.com")
                                    .username("prod.docs.typo3.com")
                                    .command("set -e\r\nset -x\r\n\r\nmkdir -p /srv/vhosts/prod.docs.typo3.com/deployment/${bamboo.buildResultKey}"),
                                new ScpTask()
                                    .description("copy result")
                                    .host("srv007.typo3.com")
                                    .username("prod.docs.typo3.com")
                                    .toRemotePath("/srv/vhosts/prod.docs.typo3.com/deployment/${bamboo.buildResultKey}")
                                    .authenticateWithKeyWithPassphrase("BAMSCRT@0@0@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", "BAMSCRT@0@0@oUXHLLxIhG+krd5vDgsSWg==")
                                    .fromArtifact(new ArtifactItem()
                                        .artifact("docs.tgz")),
                                new SshTask().authenticateWithKeyWithPassphrase("BAMSCRT@0@0@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", "BAMSCRT@0@0@oUXHLLxIhG+krd5vDgsSWg==")
                                    .description("unpack and publish docs")
                                    .host("srv007.typo3.com")
                                    .username("prod.docs.typo3.com")
                                    .command("set -e\r\nset -x\r\n\r\ncd /srv/vhosts/prod.docs.typo3.com/deployment/${bamboo.buildResultKey}\r\n\r\nmkdir documentation_result\r\ntar xf docs.tgz -C documentation_result\r\n\r\ntarget_dir=\"/srv/vhosts/prod.docs.typo3.com/site/Web/other/typo3/view-helper-reference/9.5/en-us\"\r\n\r\necho \"Deploying to ${target_dir}\"\r\n\r\nmkdir -p $target_dir\r\nrm -rf $target_dir/*\r\n\r\nmv documentation_result/FinalDocumentation/* $target_dir\r\n\r\nrm -rf /srv/vhosts/prod.docs.typo3.com/deployment/${bamboo.buildResultKey}"))
                            .requirements(new Requirement("system.hasDocker")
                                    .matchValue("1.0")
                                    .matchType(Requirement.MatchType.EQUALS),
                                new Requirement("system.builder.command.tar"))
                            .artifactSubscriptions(new ArtifactSubscription()
                                    .artifact("docs.tgz"))
                            .cleanWorkingDirectory(true)))
            .triggers(new ScheduledTrigger()
                    .cronExpression("0 40 1 ? * *"))
            .variables(new Variable("VERSION_NUMBER",
                    "9.5"))
            .planBranchManagement(new PlanBranchManagement()
                    .delete(new BranchCleanup())
                    .notificationForCommitters())
            .forceStopHungBuilds();
        return plan;
    }

    public PlanPermissions planPermission() {
        final PlanPermissions planPermission = new PlanPermissions(new PlanIdentifier("CORE", "DRF"))
            .permissions(new Permissions()
                    .userPermissions("christian.kuhn", PermissionType.ADMIN, PermissionType.VIEW, PermissionType.CLONE, PermissionType.BUILD, PermissionType.EDIT)
                    .userPermissions("daniel.siepmann", PermissionType.BUILD, PermissionType.VIEW, PermissionType.CLONE, PermissionType.ADMIN, PermissionType.EDIT)
                    .groupPermissions("TYPO3 GmbH", PermissionType.BUILD, PermissionType.VIEW, PermissionType.CLONE, PermissionType.ADMIN, PermissionType.EDIT)
                    .loggedInUserPermissions(PermissionType.VIEW)
                    .anonymousUserPermissionView());
        return planPermission;
    }

    public static void main(String... argv) {
        //By default credentials are read from the '.credentials' file.
        BambooServer bambooServer = new BambooServer("https://bamboo.typo3.com");
        final DocsRenderingFluidVH95Spec planSpec = new DocsRenderingFluidVH95Spec();

        final Plan plan = planSpec.plan();
        bambooServer.publish(plan);

        final PlanPermissions planPermission = planSpec.planPermission();
        bambooServer.publish(planPermission);
    }
}
